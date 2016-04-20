<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\Bonus\Base\Lib\Repo\Def;

use Flancer32\Lib\DataObject;
use Praxigento\Accounting\Data\Entity\Account;
use Praxigento\Accounting\Data\Entity\Transaction;
use Praxigento\Accounting\Data\Entity\Type\Asset as TypeAsset;
use Praxigento\Bonus\Base\Lib\Entity\Calculation;
use Praxigento\Bonus\Base\Lib\Entity\Cfg\Generation as CfgGeneration;
use Praxigento\Bonus\Base\Lib\Entity\Compress;
use Praxigento\Bonus\Base\Lib\Entity\Log\Rank as LogRank;
use Praxigento\Bonus\Base\Lib\Entity\Log\Sales as LogSales;
use Praxigento\Bonus\Base\Lib\Entity\Period;
use Praxigento\Bonus\Base\Lib\Entity\Rank;
use Praxigento\Bonus\Base\Lib\Entity\Type\Calc as TypeCalc;
use Praxigento\Bonus\Base\Lib\Repo\IModule;
use Praxigento\BonusBase\Config as Cfg;
use Praxigento\Core\Data\Entity\Type\Base as TypeBase;
use Praxigento\Core\Repo\Def\Base;
use Praxigento\Downline\Data\Entity\Snap;

class Module extends Base implements IModule
{
    /** @var \Praxigento\Core\Tool\IDate */
    protected $_toolDate;
    /** @var \Praxigento\Core\Repo\IGeneric */
    protected $_repoBasic;
    /** @var  \Praxigento\Core\Repo\ITransactionManager */
    protected $_manTrans;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Praxigento\Core\Repo\ITransactionManager $manTrans,
        \Praxigento\Core\Repo\IGeneric $repoBasic,
        \Praxigento\Core\Tool\IDate $toolDate
    ) {
        parent::__construct($resource);
        $this->_manTrans = $manTrans;
        $this->_repoBasic = $repoBasic;
        $this->_toolDate = $toolDate;
    }

    public function addLogSaleOrder($transId, $saleOrderId)
    {
        $bind = [
            LogSales::ATTR_TRANS_ID => $transId,
            LogSales::ATTR_SALES_ORDER_ID => $saleOrderId
        ];
        $this->_repoBasic->addEntity(LogSales::ENTITY_NAME, $bind);
    }

    public function addPeriod($calcTypeId, $dsBegin, $dsEnd)
    {
        $result = new DataObject();
        $trans = $this->_manTrans->transactionBegin();
        try {
            /* add new period */
            $periodData = [
                Period::ATTR_CALC_TYPE_ID => $calcTypeId,
                Period::ATTR_DSTAMP_BEGIN => $dsBegin,
                Period::ATTR_DSTAMP_END => $dsEnd
            ];
            $periodId = $this->_repoBasic->addEntity(Period::ENTITY_NAME, $periodData);
            $periodData[Period::ATTR_ID] = $periodId;
            $result->setData(IModule::A_PERIOD, $periodData);
            /* add related calculation */
            $dateStarted = $this->_toolDate->getUtcNowForDb();
            $calcData = [
                Calculation::ATTR_PERIOD_ID => $periodId,
                Calculation::ATTR_DATE_STARTED => $dateStarted,
                Calculation::ATTR_DATE_ENDED => null,
                Calculation::ATTR_STATE => Cfg::CALC_STATE_STARTED
            ];
            $calcId = $this->_repoBasic->addEntity(Calculation::ENTITY_NAME, $calcData);
            $this->_manTrans->transactionCommit($trans);
            $calcData[Calculation::ATTR_ID] = $calcId;
            $result->setData(IModule::A_CALC, $calcData);
        } finally {
            $this->_manTrans->transactionClose($trans);
        }
        return $result;
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
        $tblPeriod = $this->_conn->getTableName(Period::ENTITY_NAME);
        $tblCalc = $this->_conn->getTableName(Calculation::ENTITY_NAME);
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

    /**
     * @param int $calcId
     *
     * @return array [[Compress/*], ...]
     */
    public function getCompressedTree($calcId)
    {
        $where = Compress::ATTR_CALC_ID . '=' . (int)$calcId;
        $result = $this->_repoBasic->getEntities(Compress::ENTITY_NAME, null, $where);
        return $result;
    }

    public function getConfigGenerationsPercents($calcTypeId)
    {
        $result = [];
        $where = CfgGeneration::ATTR_CALC_TYPE_ID . '=' . (int)$calcTypeId;
        $rows = $this->_repoBasic->getEntities(CfgGeneration::ENTITY_NAME, null, $where);
        foreach ($rows as $row) {
            $rankId = $row[CfgGeneration::ATTR_RANK_ID];
            $gen = $row[CfgGeneration::ATTR_GENERATION];
            $percent = $row[CfgGeneration::ATTR_PERCENT];
            $result[$rankId][$gen] = $percent;
        }
        return $result;
    }

    /**
     * Return timestamp for the first transaction related to PV.
     */
    public function getFirstDateForPvTransactions()
    {
        $asAcc = 'paa';
        $asTrans = 'pat';
        $asType = 'pata';
        $tblAcc = $this->_conn->getTableName(Account::ENTITY_NAME);
        $tblTrans = $this->_conn->getTableName(Transaction::ENTITY_NAME);
        $tblType = $this->_conn->getTableName(TypeAsset::ENTITY_NAME);
        // SELECT FROM prxgt_acc_transaction pat
        $query = $this->_conn->select();
        $query->from([$asTrans => $tblTrans], [Transaction::ATTR_DATE_APPLIED]);
        // LEFT JOIN prxgt_acc_account paa ON paa.id = pat.debit_acc_id
        $on = $asAcc . '.' . Account::ATTR_ID . '=' . $asTrans . '.' . Transaction::ATTR_DEBIT_ACC_ID;
        $query->joinLeft([$asAcc => $tblAcc], $on, null);
        // LEFT JOIN prxgt_acc_type_asset pata ON paa.asset_type_id = pata.id
        $on = $asAcc . '.' . Account::ATTR_ASSET_TYPE_ID . '=' . $asType . '.' . TypeAsset::ATTR_ID;
        $query->joinLeft([$asType => $tblType], $on, null);
        // WHERE
        $where = $asType . '.' . TypeAsset::ATTR_CODE . '=' . $this->_conn->quote(Cfg::CODE_TYPE_ASSET_PV);
        $query->where($where);
        // ORDER & LIMIT
        $query->order($asTrans . '.' . Transaction::ATTR_DATE_APPLIED . ' ASC');
        $query->limit(1);
        // $sql = (string)$query;
        $result = $this->_conn->fetchOne($query);
        return $result;
    }

    public function getLatestPeriod($calcTypeId, $shouldGetLatestCalc = true, $shouldGetAllCalcs = false)
    {
        $result = new DataObject();
        /* set WHERE and ORDER BY clauses */
        $wherePeriod = Period::ATTR_CALC_TYPE_ID . '=' . (int)$calcTypeId;
        $orderPeriod = [Period::ATTR_DSTAMP_BEGIN . ' DESC'];
        /* get one only period with the biggest begin date stamp */
        $periodData = $this->_repoBasic->getEntities(Period::ENTITY_NAME, null, $wherePeriod, $orderPeriod, 1);
        if (is_array($periodData) && (count($periodData) > 0)) {
            /* get first (and only) item from result set */
            $periodData = reset($periodData);
            if ($periodData !== false) {
                $result->setData(self::A_PERIOD, $periodData);
                if ($shouldGetAllCalcs || $shouldGetLatestCalc) {
                    /* add period calculations to result set */
                    $where = Calculation::ATTR_PERIOD_ID . '=' . $periodData[Period::ATTR_ID];
                    $limit = ($shouldGetLatestCalc) ? 1 : null;
                    $order = [Calculation::ATTR_ID . ' ASC'];
                    $calcData = $this->_repoBasic->getEntities(Calculation::ENTITY_NAME, null, $where, $order,
                        $limit);
                    if (is_array($calcData) && (count($calcData) > 0)) {
                        if ($shouldGetLatestCalc) {
                            $calcData = reset($calcData);
                        }
                        $result->setData(self::A_CALC, $calcData);
                    }
                }
            }
        }
        return $result;
    }

    public function getRankIdByCode($calcTypeCode)
    {
        $tbl = $this->_conn->getTableName(Rank::ENTITY_NAME);
        $query = $this->_conn->select();
        $query->from($tbl);
        $query->where(TypeBase::ATTR_CODE . '=:code');
        // $sql = (string)$query;
        $data = $this->_conn->fetchRow($query, ['code' => $calcTypeCode]);
        $result = isset($data[TypeBase::ATTR_ID]) ? $data[TypeBase::ATTR_ID] : null;
        return $result;
    }

    public function getTypeAssetIdByCode($assetTypeCode)
    {
        $tbl = $this->_conn->getTableName(TypeAsset::ENTITY_NAME);
        /** @var  $query \Zend_Db_Select */
        $query = $this->_conn->select();
        $query->from($tbl);
        $query->where(TypeBase::ATTR_CODE . '=:code');
        // $sql = (string)$query;
        $data = $this->_conn->fetchRow($query, ['code' => $assetTypeCode]);
        $result = isset($data[TypeBase::ATTR_ID]) ? $data[TypeBase::ATTR_ID] : null;
        return $result;
    }

    public function getTypeCalcIdByCode($calcTypeCode)
    {
        $tbl = $this->_conn->getTableName(TypeCalc::ENTITY_NAME);
        /** @var  $query \Zend_Db_Select */
        $query = $this->_conn->select();
        $query->from($tbl);
        $query->where(TypeBase::ATTR_CODE . '=:code');
        // $sql = (string)$query;
        $data = $this->_conn->fetchRow($query, ['code' => $calcTypeCode]);
        $result = isset($data[TypeBase::ATTR_ID]) ? $data[TypeBase::ATTR_ID] : null;
        return $result;
    }

    public function logRank($transRef, $rankRef)
    {
        $bind = [
            LogRank::ATTR_TRANS_REF => $transRef,
            LogRank::ATTR_RANK_REF => $rankRef
        ];
        $this->_repoBasic->addEntity(LogRank::ENTITY_NAME, $bind);
    }

    /**
     * Save compressed tree.
     *
     * @param $calcId
     * @param $tree
     */
    public function saveCompressedTree($calcId, $tree)
    {
        $trans = $this->_manTrans->transactionBegin();
        try {
            foreach ($tree as $item) {
                $bind = [
                    Compress::ATTR_CALC_ID => $calcId,
                    Compress::ATTR_CUSTOMER_ID => $item[Snap::ATTR_CUSTOMER_ID],
                    Compress::ATTR_PARENT_ID => $item[Snap::ATTR_PARENT_ID]
                ];
                $this->_repoBasic->addEntity(Compress::ENTITY_NAME, $bind);
            }
            $this->_manTrans->transactionCommit($trans);
        } finally {
            $this->_manTrans->transactionClose($trans);
        }
    }

    public function updateCalcSetComplete($calcId)
    {
        $tsEnded = $this->_toolDate->getUtcNowForDb();
        $bind = [
            Calculation::ATTR_DATE_ENDED => $tsEnded,
            Calculation::ATTR_STATE => Cfg::CALC_STATE_COMPLETE
        ];
        $where = Calculation::ATTR_ID . '=' . $calcId;
        $result = $this->_repoBasic->updateEntity(Calculation::ENTITY_NAME, $bind, $where);
        return $result;
    }
}