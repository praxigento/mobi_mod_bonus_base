<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Repo\Def;

use Praxigento\Accounting\Data\Entity\Account;
use Praxigento\Accounting\Data\Entity\Transaction;
use Praxigento\Accounting\Data\Entity\Type\Asset as TypeAsset;
use Praxigento\BonusBase\Config as Cfg;
use Praxigento\BonusBase\Data\Entity\Calculation;
use Praxigento\BonusBase\Data\Entity\Period;
use Praxigento\BonusBase\Repo\IModule;
use Praxigento\Core\Repo\Def\Db;

class Module extends Db implements IModule
{
    /** @var \Praxigento\Core\Tool\IDate */
    protected $_toolDate;
    /** @var \Praxigento\Core\Repo\IGeneric */
    protected $_repoBasic;
    /** @var  \Praxigento\Core\Transaction\Database\IManager */
    protected $_manTrans;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Praxigento\Core\Transaction\Database\IManager $manTrans,
        \Praxigento\Core\Repo\IGeneric $repoBasic,
        \Praxigento\Core\Tool\IDate $toolDate
    ) {
        parent::__construct($resource);
        $this->_manTrans = $manTrans;
        $this->_repoBasic = $repoBasic;
        $this->_toolDate = $toolDate;
    }

    /**
     * SELECT
     * pbbc.*
     * FROM prxgt_bon_base_period pbbp
     * LEFT JOIN prxgt_bon_base_calc pbbc
     * ON pbbp.id = pbbc.period_id
     * WHERE pbbp.calc_type_id = 16
     * AND pbbp.dstamp_begin = '20160101'
     * AND pbbp.dstamp_end = '20160131'
     *
     * @param int $calcTypeId
     * @param string $dsBegin 'YYYYMMDD'
     * @param string $dsEnd 'YYYYMMDD'
     * @param bool $shouldGetLatestCalc
     */
    public function getCalcsForPeriod($calcTypeId, $dsBegin, $dsEnd, $shouldGetLatestCalc = false)
    {
        $conn = $this->_conn;
        $asPeriod = 'pbbp';
        $asCalc = 'pbbc';
        $tblPeriod = $this->_resource->getTableName(Period::ENTITY_NAME);
        $tblCalc = $this->_resource->getTableName(Calculation::ENTITY_NAME);
        // SELECT FROM prxgt_bon_base_period pbbp
        $query = $conn->select();
        $query->from([$asPeriod => $tblPeriod], []);
        // LEFT JOIN prxgt_bon_base_calc pbbc ON pbbp.id = pbbc.period_id
        $on = $asPeriod . '.' . Period::ATTR_ID . '=' . $asCalc . '.' . Calculation::ATTR_PERIOD_ID;
        $cols = '*';
        $query->joinLeft([$asCalc => $tblCalc], $on, $cols);
        if ($shouldGetLatestCalc) {
            // LIMIT
            $query->limit(1);
        }
        // WHERE
        $whereTypeId = $asPeriod . '.' . Period::ATTR_CALC_TYPE_ID . '=' . (int)$calcTypeId;
        $whereFrom = $asPeriod . '.' . Period::ATTR_DSTAMP_BEGIN . '=' . $conn->quote($dsBegin);
        $whereTo = $asPeriod . '.' . Period::ATTR_DSTAMP_END . '=' . $conn->quote($dsEnd);
        $query->where("$whereTypeId AND $whereFrom AND $whereTo");
        // $sql = (string)$query;
        $result = $conn->fetchAll($query);
        if ($shouldGetLatestCalc && is_array($result)) {
            $result = reset($result);
        }
        return $result;
    }



}