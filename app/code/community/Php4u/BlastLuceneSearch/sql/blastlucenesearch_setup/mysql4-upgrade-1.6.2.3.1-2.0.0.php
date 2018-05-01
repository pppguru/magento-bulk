<?php
/**
 * @category   Php4u
 * @package    Php4u_BlastLuceneSearch
 * @author     Marcin Szterling <marcin@php4u.co.uk>
 * @copyright  Php4u Marcin Szterling (c) 2013
 * @license http://php4u.co.uk/licence/
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 * Any form of ditribution, sell, transfer forbidden see licence above
 */
    $installer = $this;
    /* @var $installer Php4u_BlastLuceneSearch_Model_Resource_Eav_Mysql4_Setup */
    $installer->startSetup();
    
    $table = $installer->getConnection()->newTable($installer->getTable('blastlucenesearch/report'))
	    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
	    		'unsigned' => true,
	    		'nullable' => false,
	    		'primary' => true,
	    		'identity' => true,
	    ), 'Report Row ID')
	    ->addColumn('query', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
	    		'nullable' => false,
	    ), 'Query / Product Name')
	    ->addColumn('results', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
	    		'nullable' => false,
	    		'unsigned' => true,
	    ), 'Number of results for the query')
	    ->addColumn('report_type', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
	    		'nullable' => false,
	    		'unsigned' => true,
	    ), 'Report type')
	    ->addColumn('period', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
	    		'nullable' => false,
	    ), 'Period')
	    ->addColumn('uses', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
	    		'nullable' => false,
	    		'unsigned' => true,
	    ), 'Number of uses of the query')
	    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
	    ), 'Updated at');
    $installer->getConnection()->createTable($table);
    $installer->endSetup();
