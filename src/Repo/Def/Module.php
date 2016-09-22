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

    /**
     * Return timestamp for the first transaction related to PV.
     */
    public function getFirstDateForPvTransactions()
    {
        $asAcc = 'paa';
        $asTrans = 'pat';
        $asType = 'pata';
        $tblAcc = $this->_resource->getTableName(Account::ENTITY_NAME);
        $tblTrans = $this->_resource->getTableName(Transaction::ENTITY_NAME);
        $tblType = $this->_resource->getTableName(TypeAsset::ENTITY_NAME);
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


}