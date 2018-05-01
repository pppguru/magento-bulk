var CompetitorPrice = Class.create();
CompetitorPrice.prototype = {

    initialize: function(eHostUrl, eMaxDisplayedSeller, eSortMode, eConfigurationUrl)
    {
        this.hostUrl = eHostUrl;
        this.products = {};
        this.lastProductAddedTimeStamp = null;
        this.productsInNewStatus = 0;
        this.autoReloadCount = 0;
        this.autoReloadMax = 10;
        this.maxDisplayedSeller = eMaxDisplayedSeller;
        this.sortMode = eSortMode;
        this.configurationUrl = eConfigurationUrl;
        this.hasRequestSchedule = true;

        setInterval("competitorPriceObj.checkForServerRequest();", 500);
    },


    addProduct: function(id, channel, fieldName, value)
    {
        var item = {};
        item['channel'] = channel;
        item[fieldName] = value;
        this.products[id] = item;

        this.lastProductAddedTimeStamp = new Date().getTime();

        competitorPriceObj.autoReloadCount = 0;
    },

    checkForServerRequest: function()
    {
        if (this.lastProductAddedTimeStamp)
        {
            var diff =  (new Date().getTime()) - this.lastProductAddedTimeStamp;
            if (diff > 500)
            {
                this.lastProductAddedTimeStamp = null;
                this.execute();
            }
        }
    },

    execute: function()
    {
        this.hasRequestSchedule = false;

        var cells = document.getElementsByClassName('competitor_price');
        for(var i = 0; i < cells.length; i++) {
            if(i % 2 == 0 ) {
                var color = '#E0EEE0';
            } else {
                var color = '#F0FFF0';
            }

            cells[i].parentElement.style.backgroundColor = color;
        }

        if (this.products.length == 0)
            return;

        //document.getElementById('request_count').innerHTML += '.';
        new Ajax.Request(
            this.hostUrl,
            {
                method: 'post',
                loaderArea: false,
                onSuccess: function onSuccess(transport)
                {
                    competitorPriceObj.processResult(transport);
                    this.products = {};
                },
                onFailure: function onFailure(transport)
                {
                    alert('An error occured');
                    this.products = {};
                },
                parameters: this.buildBody()
            }
        );
    },

    buildBody: function()
    {
        return "action=prices&products=" + Object.toJSON(this.products);
    },

    processResult: function(response)
    {
        var response = response._getResponseJSON();

        var hasError = false;
        if (response.errors)
        {
            var errorMessage = response.errors.join();
            errorMessage += ' : <a href="' + this.configurationUrl + '">' + Translator.translate('please check your configuration') + '</a>';
            hasError = true;
        }
        else
        {

            var cells = document.getElementsByClassName('credit_total');
            for(var i = 0; i < cells.length; i++) {
                cells[i].innerHTML = response.credits;
            }

            if ((response.credits < 30) && (document.getElementById('competitor_price_low_credits_warning')))
                document.getElementById('competitor_price_low_credits_warning').style.display = '';
            
        }

        var products = response.offers;

        competitorPriceObj.productsInNewStatus = 0;

        for(var productId in this.products) {
            var divElement = document.getElementById('competitor_price_' + productId);
            if (divElement)
            {
                var content = '';
                if (hasError)
                    content = errorMessage;
                else
                {
                    var product = products[productId];
                    switch (true) {
                        case product == undefined:
                            content = '<font color="#888888">' + Translator.translate('No barcode available') + '</font>';
                            break;
                        case product.status == 'EXCLUDED':
                            content = '<font color="#888888">' + Translator.translate('This product is excluded') + '</font>';
                            break;
                        case product.status == 'ASSOCIATED':
                            content = this.generateTableOffer(product, this.products[productId]['channel'], divElement.parentNode.style.backgroundColor);
                            break;
                        case product.status == 'NOT_ASSOCIATED':
                            content = '<font color="#888888">' + Translator.translate('No result') + '</font>';
                            break;
                        case product.status == 'BLOCKED':
                            content = Translator.translate('Your credits are over') + ' : <a href="https://www.boostmyshop.com/Bms/Front/Credits">' + Translator.translate('buy credits') + '</a>';
                            break;
                        case product.status == 'ERROR':
                            content = product.message;
                            break;
                        case product.status == 'NOT_MONITORED':
                            content = '<center><input type="button" style="background-color: #ffffff; border: 1px solid black; padding: 3px; margin-top: 5px;" onclick="competitorPriceObj.enableProductMonitoring(' + productId + ')" value="' + Translator.translate('Watch this product') + '"></center>';
                            break;
                        case product.status == 'NEW':
                        case product.status == 'PENDING_CRAWL':
                        default:
                            content = Translator.translate('Matching in progress, please wait');
                            competitorPriceObj.productsInNewStatus += 1;
                            break;
                    }
                }
                divElement.innerHTML = content;
            }


        }

        //if has produtcs with matching in progress status, schedule next call for update 15s after
        if (competitorPriceObj.productsInNewStatus > 0) {
            if (competitorPriceObj.autoReloadCount < competitorPriceObj.autoReloadMax) {
                var timeOut = competitorPriceObj.getTimeOut();
                if (timeOut > 0 && !this.hasRequestSchedule) {
                    setTimeout("competitorPriceObj.execute();", timeOut);
                    this.hasRequestSchedule = true;
                }
                competitorPriceObj.autoReloadCount++;
            }
        }
    },

    getTimeOut: function()
    {
        switch(this.autoReloadCount)
        {
            case 0:
                return 10000;
            case 1:
            case 2:
            case 3:
                return 15000;
            case 4:
            case 5:
            case 6:
            case 7:
            case 8:
                return 60000;
            default:
                return 0;
        }
    },

    generateTableOffer: function(product, channel, backgroundColor)
    {
        var rightCellStyle = 'style="background-color: ' + backgroundColor + '; border: 1px solid #ccc; text-align:right; width:50px"';
        var leftCellStyle = 'style="background-color: ' + backgroundColor + '; border: 1px solid #ccc; text-align:left"';

        var maxOffers = product.offers.length > competitorPriceObj.maxDisplayedSeller ? competitorPriceObj.maxDisplayedSeller : product.offers.length;
        if (maxOffers == 0)
            return Translator.translate('No offer for this product');

        var table = '<table style="border: 0; border-collapse: collapse" cellpadding="0" cellspacing="0">';
        table += '<tr>' +
            '<th ' + leftCellStyle + ' width="300px">' + Translator.translate('Competitor') + '</th>' +
            '<th ' + leftCellStyle + '>' + Translator.translate('Price') + '</th>' +
            '<th ' + leftCellStyle + '>' + Translator.translate('Shipping') + '</th>' +
            '<th ' + leftCellStyle + '>' + Translator.translate('Total') + '</th>' +
            '</tr>'
        ;

        if (competitorPriceObj.sortMode == 'total')
        {
            product.offers.sort(function(a, b) {
                var totalA = a.price + a.shipping;
                var totalB = b.price + b.shipping;
                return (totalA > totalB);
            });
        }

        for(var i = 0; i < maxOffers; i++) {
            var offer = product.offers[i];
            table += '<tr>' +
                '<td ' + leftCellStyle + '>' + (i + 1) +'. ' + offer.competitor + '</td>' +
                '<td ' + rightCellStyle + '>' + offer.price + product.currency + '</td>' +
                '<td ' + rightCellStyle + '>' + offer.shipping + product.currency + '</td>' +
                '<td ' + rightCellStyle + '>' + Math.round((offer.price + offer.shipping)*100)/100 + product.currency + '</td>' +
                '</tr>'
            ;
        }

        var totalOffers = product.offers.length == 10 ? '10+' : product.offers.length;
        var productLink = product.link ? '<a href="' + product.link + '" target="_blank">' + Translator.translate('See on') + ' ' + channel.split('_')[0] + '</a>' : '';
        table += '<tr><td ' + leftCellStyle + ' colspan="4">' + totalOffers + ' ' + Translator.translate('offers for this product') + ' ' + productLink +  '</td></tr>';

        table += '</table>';

        return table;
    },

    enableProductMonitoring: function(productId)
    {
        var body = "action=add_to_monitoring&product_id=" + productId + "&product_data=" + Object.toJSON(this.products[productId]);
        new Ajax.Request(
            this.hostUrl,
            {
                method: 'post',
                onSuccess: function onSuccess(transport)
                {
                    var result = transport._getResponseJSON();
                    competitorPriceObj.addProduct(result.product_id, result.channel, 'ean', result.ean);
                },
                onFailure: function onFailure(transport)
                {
                    alert('An error occured');
                },
                parameters: body
            }
        );
    },

    addOffersInProductView: function(barcode, channel)
    {

        var params = "action=prices&products=" + '{"1":{"channel":"' + channel +'","ean":"' + barcode +'"}}';

        new Ajax.Request(
            this.hostUrl,
            {
                method: 'post',
                loaderArea: false,
                onSuccess: function onSuccess(transport)
                {
                    var response = transport._getResponseJSON();

                    var content = '';

                    if (response.offers[1].status == 'ASSOCIATED')
                    {
                        var offers = response.offers[1].offers;
                        var currency = response.offers[1].currency;

                        content += '<table border="1" cellspacing="0" cellpadding="3" width="500">';

                        content += '<tr>';
                        content += '<th style="padding: 3px; background-color: #D7E5EF;"></th>';
                        content += '<th style="padding: 3px; background-color: #D7E5EF;">Price (' + currency + ')</th>';
                        content += '<th style="padding: 3px; background-color: #D7E5EF;">Shipping (' + currency + ')</th>';
                        content += '<th style="padding: 3px; background-color: #D7E5EF;">Total (' + currency + ')</th>';
                        content += '</tr>';

                        var max = offers.length;
                        if (max > 5)
                            max = 5;
                        for(var i=0;i<max;i++)
                        {
                            content += '<tr>';
                            content += '<td style="padding: 3px;">' + offers[i].competitor + '</td>';
                            content += '<td style="padding: 3px;" align="right">' + offers[i].price.toFixed(2) + '</td>';
                            content += '<td style="padding: 3px;" align="right">' + offers[i].shipping.toFixed(2) + '</td>';
                            content += '<td style="padding: 3px;" align="right">' + (offers[i].price + offers[i].shipping).toFixed(2) + '</td>';
                            content += '</tr>';
                        }

                        if (offers.length == 0)
                        {
                            content += '<tr>';
                            content += '<td style="padding: 3px;" colspan="4">No offers found</td>';
                            content += '</tr>';
                        }

                        content += '</table>';

                        var channelName = response.offers[1].channel;
                        var t = channelName.split('_');
                        channelName = t[0] + '.' + t[1];

                        var nb = offers.length;
                        if (nb == 10)
                            nb += '+ ';
                        content += '<p>' + nb + ' offers available on ';
                        if (response.offers[1].link)
                            content += '<a target="_blank" href="' + response.offers[1].link + '">'
                        content += channelName;
                        if (response.offers[1].link)
                            content += '</a>';
                        content += ' - all prices include taxes';
                        content += '</p>';


                        var priceElement = $('price');
                        if (priceElement && priceElement.parentNode)
                        {
                            var trNode = priceElement.parentNode.parentNode;

                            var newTR = '<tr><td class="label"><label for="price">Competitors</label></td><td class="value">';
                            newTR += content;
                            newTR += '</td><td class="scope-label"><span class="nobr"></span></td></tr>';

                            trNode.insert({'after': newTR});
                        }

                    }

                },
                onFailure: function onFailure(transport)
                {
                    alert('An error occured');
                },
                parameters: params
            }
        );


    }
}

