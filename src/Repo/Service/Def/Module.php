<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\BonusBase\Repo\Service\Def;

use Praxigento\Accounting\Data\Entity\Account as EAccount;
use Praxigento\Accounting\Data\Entity\Transaction as ETransaction;
use Praxigento\Accounting\Data\Entity\Type\Asset as ETypeAsset;
use Praxigento\BonusBase\Config as Cfg;
use Praxigento\BonusBase\Data\Entity\Calculation as ECalculation;
use Praxigento\BonusBase\Data\Entity\Period as EPeriod;

class Module
    extends \Praxigento\Core\Repo\Def\Db
    implements \Praxigento\BonusBase\Repo\Service\IModule
{
    /** @var \Praxigento\BonusBase\Repo\Entity\ICalculation */
    protected $_repoCalc;
    /** @var \Praxigento\BonusBase\Repo\Entity\IPeriod */
    protected $_repoPeriod;
    /** @var \Praxigento\Core\Tool\IDate */
    protected $_toolDate;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Praxigento\BonusBase\Repo\Entity\ICalculation $repoCalc,
        \Praxigento\BonusBase\Repo\Entity\IPeriod $repoPeriod,
        \Praxigento\Core\Tool\IDate $toolDate
    ) {
        parent::__construct($resource);
        $this->_repoCalc = $repoCalc;
        $this->_repoPeriod = $repoPeriod;
        $this->_toolDate = $toolDate;
    }

    public function getFirstDateForPvTransactions()
    {
        $asAcc = 'paa';
        $asTrans = 'pat';
        $asType = 'pata';
        $tblAcc = $this->resource->getTableName(EAccount::ENTITY_NAME);
        $tblTrans = $this->resource->getTableName(ETransaction::ENTITY_NAME);
        $tblType = $this->resource->getTableName(ETypeAsset::ENTITY_NAME);
        // SELECT FROM prxgt_acc_transaction pat
        $query = $this->conn->select();
        $query->from([$asTrans => $tblTrans], [ETransaction::ATTR_DATE_APPLIED]);
        // LEFT JOIN prxgt_acc_account paa ON paa.id = pat.debit_acc_id
        $on = $asAcc . '.' . EAccount::ATTR_ID . '=' . $asTrans . '.' . ETransaction::ATTR_DEBIT_ACC_ID;
        $query->joinLeft([$asAcc => $tblAcc], $on, null);
        // LEFT JOIN prxgt_acc_type_asset pata ON paa.asset_type_id = pata.id
        $on = $asAcc . '.' . EAccount::ATTR_ASSET_TYPE_ID . '=' . $asType . '.' . ETypeAsset::ATTR_ID;
        $query->joinLeft([$asType => $tblType], $on, null);
        // WHERE
        $where = $asType . '.' . ETypeAsset::ATTR_CODE . '=' . $this->conn->quote(Cfg::CODE_TYPE_ASSET_PV);
        $query->where($where);
        // ORDER & LIMIT
        $query->order($asTrans . '.' . ETransaction::ATTR_DATE_APPLIED . ' ASC');
        $query->limit(1);
        //
        $result = $this->conn->fetchOne($query);
        return $result;
    }

    public function getLastCalcForPeriodByDates($calcTypeId, $dsBegin, $dsEnd)
    {
        $result = null;
        $conn = $this->conn;
        $asPeriod = 'pbbp';
        $asCalc = 'pbbc';
        $tblPeriod = $this->resource->getTableName(EPeriod::ENTITY_NAME);
        $tblCalc = $this->resource->getTableName(ECalculation::ENTITY_NAME);
        // SELECT FROM prxgt_bon_base_period pbbp
        $query = $conn->select();
        $query->from([$asPeriod => $tblPeriod], []);
        // LEFT JOIN prxgt_bon_base_calc pbbc ON pbbp.id = pbbc.period_id
        $on = $asPeriod . '.' . EPeriod::ATTR_ID . '=' . $asCalc . '.' . ECalculation::ATTR_PERIOD_ID;
        $cols = '*';
        $query->joinLeft([$asCalc => $tblCalc], $on, $cols);
        // ORDER
        $query->order(ECalculation::ATTR_ID . ' DESC');
        // LIMIT
        $query->limit(1);
        // WHERE
        $whereTypeId = $asPeriod . '.' . EPeriod::ATTR_CALC_TYPE_ID . '=' . (int)$calcTypeId;
        $whereFrom = $asPeriod . '.' . EPeriod::ATTR_DSTAMP_BEGIN . '=' . $conn->quote($dsBegin);
        $whereTo = $asPeriod . '.' . EPeriod::ATTR_DSTAMP_END . '=' . $conn->quote($dsEnd);
        $query->where("$whereTypeId AND $whereFrom AND $whereTo");
        //
        $rs = $conn->fetchAll($query);
        if (is_array($rs)) {
            $data = reset($rs);
            $result = new ECalculation($data);
        }
        return $result;
    }

    public function getLastCalcForPeriodById($periodId)
    {
        $result = null;
        $where = ECalculation::ATTR_PERIOD_ID . '=' . (int)$periodId;
        $limit = 1;
        $order = [ECalculation::ATTR_ID . ' ASC'];
        $rs = $this->_repoCalc->get($where, $order, $limit);
        if (is_array($rs) && count($rs)) {
            $data = reset($rs);
            $result = $data;
        }
        return $result;
    }

    public function getLastPeriodByCalcType($calcTypeId)
    {
        $result = null;
        /* set WHERE and ORDER BY clauses */
        $where = EPeriod::ATTR_CALC_TYPE_ID . '=' . (int)$calcTypeId;
        $order = [EPeriod::ATTR_DSTAMP_BEGIN . ' DESC'];
        /* get one only period with the biggest begin date stamp */
        $rs = $this->_repoPeriod->get($where, $order, 1);
        if (is_array($rs) && count($rs)) {
            $data = reset($rs);
            $result = $data;
        }
        return $result;
    }

    public function markCalcComplete($calcId)
    {
        $tsEnded = $this->_toolDate->getUtcNowForDb();
        $bind = [
            ECalculation::ATTR_DATE_ENDED => $tsEnded,
            ECalculation::ATTR_STATE => Cfg::CALC_STATE_COMPLETE
        ];
        $where = ECalculation::ATTR_ID . '=' . $calcId;
        $result = $this->_repoCalc->update($bind, $where);
        return $result;
    }
}