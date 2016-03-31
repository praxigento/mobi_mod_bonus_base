<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\Bonus\Base\Lib\Repo\Def;

use Flancer32\Lib\DataObject;
use Praxigento\Accounting\Lib\Entity\Account;
use Praxigento\Accounting\Lib\Entity\Transaction;
use Praxigento\Accounting\Lib\Entity\Type\Asset as TypeAsset;
use Praxigento\BonusBase\Config as Cfg;
use Praxigento\Bonus\Base\Lib\Entity\Calculation;
use Praxigento\Bonus\Base\Lib\Entity\Cfg\Generation as CfgGeneration;
use Praxigento\Bonus\Base\Lib\Entity\Compress;
use Praxigento\Bonus\Base\Lib\Entity\Log\Rank as LogRank;
use Praxigento\Bonus\Base\Lib\Entity\Log\Sales as LogSales;
use Praxigento\Bonus\Base\Lib\Entity\Period;
use Praxigento\Bonus\Base\Lib\Entity\Rank;
use Praxigento\Bonus\Base\Lib\Entity\Type\Calc as TypeCalc;
use Praxigento\Bonus\Base\Lib\Repo\IModule;
use Praxigento\Core\Lib\Entity\Type\Base as TypeBase;
use Praxigento\Core\Lib\Repo\Base;
use Praxigento\Downline\Lib\Entity\Snap;

class Module extends Base implements IModule {
    /** @var \Praxigento\Core\Lib\Tool\Date */
    protected $_toolDate;

    public function __construct(
        \Praxigento\Core\Lib\Repo\IBasic $repoBasic,
        \Praxigento\Core\Lib\Tool\Date $toolDate
    ) {
        parent::__construct($repoBasic);
        $this->_toolDate = $toolDate;
    }

    public function addLogSaleOrder($transId, $saleOrderId) {
        $bind = [
            LogSales::ATTR_TRANS_ID       => $transId,
            LogSales::ATTR_SALES_ORDER_ID => $saleOrderId
        ];
        $this->_repoBasic->addEntity(LogSales::ENTITY_NAME, $bind);
    }

    public function addPeriod($calcTypeId, $dsBegin, $dsEnd) {
        $result = new DataObject();
        $this->_getConn()->beginTransaction();
        try {
            /* add new period */
            $periodData = [
                Period::ATTR_CALC_TYPE_ID => $calcTypeId,
                Period::ATTR_DSTAMP_BEGIN => $dsBegin,
                Period::ATTR_DSTAMP_END   => $dsEnd
            ];
            $periodId = $this->_repoBasic->addEntity(Period::ENTITY_NAME, $periodData);
            $periodData[Period::ATTR_ID] = $periodId;
            $result->setData(IModule::A_PERIOD, $periodData);
            /* add related calculation */
            $dateStarted = $this->_toolDate->getUtcNowForDb();
            $calcData = [
                Calculation::ATTR_PERIOD_ID    => $periodId,
                Calculation::ATTR_DATE_STARTED => $dateStarted,
                Calculation::ATTR_DATE_ENDED   => null,
                Calculation::ATTR_STATE        => Cfg::CALC_STATE_STARTED
            ];
            $calcId = $this->_repoBasic->addEntity(Calculation::ENTITY_NAME, $calcData);
            $this->_getConn()->commit();
            $calcData[Calculation::ATTR_ID] = $calcId;
            $result->setData(IModule::A_CALC, $calcData);
        } finally {
            if(is_null($result->getData(IModule::A_CALC))) {
                $this->_getConn()->rollBack();
            }
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
     * @param int    $calcTypeId
     * @param string $dsBegin 'YYYYMMDD'
     * @param string $dsEnd 'YYYYMMDD'
     * @param bool   $shouldGetLatestCalc
     */
    public function getCalcsForPeriod($calcTypeId, $dsBegin, $dsEnd, $shouldGetLatestCalc = false) {
        $conn = $this->_getConn();
        $asPeriod = 'pbbp';
        $asCalc = 'pbbc';
        $tblPeriod = $this->_getTableName(Period::ENTITY_NAME);
        $tblCalc = $this->_getTableName(Calculation::ENTITY_NAME);
        // SELECT FROM prxgt_bon_base_period pbbp
        $query = $conn->select();
        $query->from([ $asPeriod => $tblPeriod ], [ ]);
        // LEFT JOIN prxgt_bon_base_calc pbbc ON pbbp.id = pbbc.period_id
        $on = $asPeriod . '.' . Period::ATTR_ID . '=' . $asCalc . '.' . Calculation::ATTR_PERIOD_ID;
        $cols = '*';
        $query->joinLeft([ $asCalc => $tblCalc ], $on, $cols);
        if($shouldGetLatestCalc) {
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
        if($shouldGetLatestCalc && is_array($result)) {
            $result = reset($result);
        }
        return $result;
    }

    /**
     * @param int $calcId
     *
     * @return array [[Compress/*], ...]
     */
    public function getCompressedTree($calcId) {
        $where = Compress::ATTR_CALC_ID . '=' . (int)$calcId;
        $result = $this->_repoBasic->getEntities(Compress::ENTITY_NAME, null, $where);
        return $result;
    }

    public function getConfigGenerationsPercents($calcTypeId) {
        $result = [ ];
        $where = CfgGeneration::ATTR_CALC_TYPE_ID . '=' . (int)$calcTypeId;
        $rows = $this->_repoBasic->getEntities(CfgGeneration::ENTITY_NAME, null, $where);
        foreach($rows as $row) {
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
    public function getFirstDateForPvTransactions() {
        $asAcc = 'paa';
        $asTrans = 'pat';
        $asType = 'pata';
        $tblAcc = $this->_getTableName(Account::ENTITY_NAME);
        $tblTrans = $this->_getTableName(Transaction::ENTITY_NAME);
        $tblType = $this->_getTableName(TypeAsset::ENTITY_NAME);
        // SELECT FROM prxgt_acc_transaction pat
        $query = $this->_getConn()->select();
        $query->from([ $asTrans => $tblTrans ], [ Transaction::ATTR_DATE_APPLIED ]);
        // LEFT JOIN prxgt_acc_account paa ON paa.id = pat.debit_acc_id
        $on = $asAcc . '.' . Account::ATTR_ID . '=' . $asTrans . '.' . Transaction::ATTR_DEBIT_ACC_ID;
        $query->joinLeft([ $asAcc => $tblAcc ], $on, null);
        // LEFT JOIN prxgt_acc_type_asset pata ON paa.asset_type_id = pata.id
        $on = $asAcc . '.' . Account::ATTR_ASSET_TYPE__ID . '=' . $asType . '.' . TypeAsset::ATTR_ID;
        $query->joinLeft([ $asType => $tblType ], $on, null);
        // WHERE
        $where = $asType . '.' . TypeAsset::ATTR_CODE . '=' . $this->_getConn()->quote(Cfg::CODE_TYPE_ASSET_PV);
        $query->where($where);
        // ORDER & LIMIT
        $query->order($asTrans . '.' . Transaction::ATTR_DATE_APPLIED . ' ASC');
        $query->limit(1);
        // $sql = (string)$query;
        $result = $this->_getConn()->fetchOne($query);
        return $result;
    }

    public function getLatestPeriod($calcTypeId, $shouldGetLatestCalc = true, $shouldGetAllCalcs = false) {
        $result = new DataObject();
        /* set WHERE and ORDER BY clauses */
        $wherePeriod = Period::ATTR_CALC_TYPE_ID . '=' . (int)$calcTypeId;
        $orderPeriod = [ Period::ATTR_DSTAMP_BEGIN . ' DESC' ];
        /* get one only period with the biggest begin date stamp */
        $periodData = $this->_repoBasic->getEntities(Period::ENTITY_NAME, null, $wherePeriod, $orderPeriod, 1);
        if(is_array($periodData) && (count($periodData) > 0)) {
            /* get first (and only) item from result set */
            $periodData = reset($periodData);
            if($periodData !== false) {
                $result->setData(self::A_PERIOD, $periodData);
                if($shouldGetAllCalcs || $shouldGetLatestCalc) {
                    /* add period calculations to result set */
                    $where = Calculation::ATTR_PERIOD_ID . '=' . $periodData[Period::ATTR_ID];
                    $limit = ($shouldGetLatestCalc) ? 1 : null;
                    $order = [ Calculation::ATTR_ID . ' ASC' ];
                    $calcData = $this->_repoBasic->getEntities(Calculation::ENTITY_NAME, null, $where, $order, $limit);
                    if(is_array($calcData) && (count($calcData) > 0)) {
                        if($shouldGetLatestCalc) {
                            $calcData = reset($calcData);
                        }
                        $result->setData(self::A_CALC, $calcData);
                    }
                }
            }
        }
        return $result;
    }

    public function getRankIdByCode($calcTypeCode) {
        $tbl = $this->_getTableName(Rank::ENTITY_NAME);
        $query = $this->_getConn()->select();
        $query->from($tbl);
        $query->where(TypeBase::ATTR_CODE . '=:code');
        // $sql = (string)$query;
        $data = $this->_getConn()->fetchRow($query, [ 'code' => $calcTypeCode ]);
        $result = isset($data[TypeBase::ATTR_ID]) ? $data[TypeBase::ATTR_ID] : null;
        return $result;
    }

    public function getTypeAssetIdByCode($assetTypeCode) {
        $tbl = $this->_getTableName(TypeAsset::ENTITY_NAME);
        /** @var  $query \Zend_Db_Select */
        $query = $this->_getConn()->select();
        $query->from($tbl);
        $query->where(TypeBase::ATTR_CODE . '=:code');
        // $sql = (string)$query;
        $data = $this->_getConn()->fetchRow($query, [ 'code' => $assetTypeCode ]);
        $result = isset($data[TypeBase::ATTR_ID]) ? $data[TypeBase::ATTR_ID] : null;
        return $result;
    }

    public function getTypeCalcIdByCode($calcTypeCode) {
        $tbl = $this->_getTableName(TypeCalc::ENTITY_NAME);
        /** @var  $query \Zend_Db_Select */
        $query = $this->_getConn()->select();
        $query->from($tbl);
        $query->where(TypeBase::ATTR_CODE . '=:code');
        // $sql = (string)$query;
        $data = $this->_getConn()->fetchRow($query, [ 'code' => $calcTypeCode ]);
        $result = isset($data[TypeBase::ATTR_ID]) ? $data[TypeBase::ATTR_ID] : null;
        return $result;
    }

    public function logRank($transRef, $rankRef) {
        $bind = [
            LogRank::ATTR_TRANS_REF => $transRef,
            LogRank::ATTR_RANK_REF  => $rankRef
        ];
        $this->_repoBasic->addEntity(LogRank::ENTITY_NAME, $bind);
    }

    /**
     * Save compressed tree.
     *
     * @param $calcId
     * @param $tree
     */
    public function saveCompressedTree($calcId, $tree) {
        $this->_getConn()->beginTransaction();
        $isCommited = false;
        try {
            foreach($tree as $item) {
                $bind = [
                    Compress::ATTR_CALC_ID     => $calcId,
                    Compress::ATTR_CUSTOMER_ID => $item[Snap::ATTR_CUSTOMER_ID],
                    Compress::ATTR_PARENT_ID   => $item[Snap::ATTR_PARENT_ID]
                ];
                $this->_repoBasic->addEntity(Compress::ENTITY_NAME, $bind);
            }
            $this->_getConn()->commit();
            $isCommited = true;
        } finally {
            if(!$isCommited) {
                $this->_getConn()->rollBack();
            }
        }
    }

    public function updateCalcSetComplete($calcId) {
        $tsEnded = $this->_toolDate->getUtcNowForDb();
        $bind = [
            Calculation::ATTR_DATE_ENDED => $tsEnded,
            Calculation::ATTR_STATE      => Cfg::CALC_STATE_COMPLETE
        ];
        $where = Calculation::ATTR_ID . '=' . $calcId;
        $result = $this->_repoBasic->updateEntity(Calculation::ENTITY_NAME, $bind, $where);
        return $result;
    }
}