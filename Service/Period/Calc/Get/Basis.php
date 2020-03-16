<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Service\Period\Calc\Get;

use Praxigento\BonusBase\Api\Service\Period\Calc\Get\Basis\Response as AResponse;
use Praxigento\BonusBase\Config as Cfg;
use Praxigento\BonusBase\Repo\Data\Calculation as ECalc;
use Praxigento\BonusBase\Repo\Data\Type\Calc as ECalcType;
use Praxigento\BonusBase\Repo\Query\Period\Calcs\Builder as QBGetCalc;

class Basis
    implements \Praxigento\BonusBase\Api\Service\Period\Calc\Get\Basis
{
    /** @var \Praxigento\BonusBase\Repo\Dao\Calculation */
    protected $daoCalc;
    /** @var \Praxigento\BonusBase\Repo\Dao\Period */
    protected $daoPeriod;
    /** @var \Praxigento\Core\Api\Helper\Period */
    protected $hlpPeriod;
    /** @var \Praxigento\Core\Api\App\Logger\Main */
    protected $logger;
    /** @var \Praxigento\BonusBase\Service\Period\Calc\IAdd */
    protected $procCalcAdd;
    /** @var \Praxigento\Accounting\Repo\Query\Trans\Get\FirstDate\ByAssetType\Builder */
    protected $qbGetFirstDate;
    /** @var \Praxigento\BonusBase\Repo\Query\Period\Calcs\Builder */
    protected $qbGetPeriod;

    public function __construct(
        \Praxigento\Core\Api\App\Logger\Main $logger,
        \Praxigento\Core\Api\Helper\Period $hlpPeriod,
        \Praxigento\BonusBase\Repo\Dao\Calculation $daoCalc,
        \Praxigento\BonusBase\Repo\Dao\Period $daoPeriod,
        \Praxigento\BonusBase\Repo\Query\Period\Calcs\Builder $qbGetPeriod,
        \Praxigento\Accounting\Repo\Query\Trans\Get\FirstDate\ByAssetType\Builder $qbGetFirstDate,
        \Praxigento\BonusBase\Service\Period\Calc\IAdd $procCalcAdd
    ) {
        $this->logger = $logger;
        $this->hlpPeriod = $hlpPeriod;
        $this->daoCalc = $daoCalc;
        $this->daoPeriod = $daoPeriod;
        $this->qbGetPeriod = $qbGetPeriod;
        $this->qbGetFirstDate = $qbGetFirstDate;
        $this->procCalcAdd = $procCalcAdd;
    }

    /**
     * Registry new period and related calculation.
     *
     * @param $dateFirst
     * @param $periodType
     * @param $calcTypeCode
     * @return [$periodId, $calcId, $err]
     */
    private function addPeriodCalc($dateFirst, $periodType, $calcTypeCode)
    {
        /* define period begin/end */
        $periodMonth = $this->hlpPeriod->getPeriodCurrent($dateFirst, +1, $periodType);
        $dsBegin = $this->hlpPeriod->getPeriodFirstDate($periodMonth);
        $dsEnd = $this->hlpPeriod->getPeriodLastDate($periodMonth);

        /* result data */
        $periodId = $calcId = $err = null;
        /* register new period & calc */
        $ctxAdd = new \Praxigento\Core\Data();
        $ctxAdd->set($this->procCalcAdd::CTX_IN_CALC_TYPE_CODE, $calcTypeCode);
        $ctxAdd->set($this->procCalcAdd::CTX_IN_DSTAMP_BEGIN, $dsBegin);
        $ctxAdd->set($this->procCalcAdd::CTX_IN_DSTAMP_END, $dsEnd);
        $this->procCalcAdd->exec($ctxAdd);
        $success = $ctxAdd->get($this->procCalcAdd::CTX_OUT_SUCCESS);
        if ($success) {
            $periodId = $ctxAdd->get($this->procCalcAdd::CTX_OUT_PERIOD_ID);
            $calcId = $ctxAdd->get($this->procCalcAdd::CTX_OUT_CALC_ID);
            $this->logger->info("New period (#$periodId) and related calculation (#$calcId) are created.");
        } else {
            $err = $ctxAdd->get($this->procCalcAdd::ERR_PERIOD_END_IS_IN_FUTURE);
            if ($err) {
                $this->logger->warning("End of the adding period ($dsEnd) is in the future.");
            }
        }
        /* return results as array */
        return [$periodId, $calcId, $err];
    }

    public function execute($req)
    {
        $result = new \Praxigento\BonusBase\Api\Service\Period\Calc\Get\Basis\Response();
        /* get working data from context */
        $calcTypeCode = $req->getCalcCode();
        $assetTypeCode = $req->getAssetTypeCode();
        $periodType = $req->getPeriodType() ?? \Praxigento\Core\Api\Helper\Period::TYPE_MONTH;

        /**
         * perform processing
         */
        $this->logger->info("'Get basis period calculation' processing is started ($calcTypeCode).");
        /* get the last period data for given calculation type */
        $periodLast = $this->queryLastPeriod($calcTypeCode);
        if (empty($periodLast)) {
            $this->logger->info("There is no period for '$calcTypeCode' calculation  yet.");
            /* get first date for related asset */
            $dateFirst = $this->queryFirstDate($assetTypeCode);
            if ($dateFirst === false) {
                $this->logger->info("There is no '$assetTypeCode' transactions yet. Nothing to do.");
                $result->setErrorCode(AResponse::ERR_NO_TRANS_YET);
            } else {
                $this->logger->info("First '$assetTypeCode' transaction was performed at '$dateFirst'.");
                list($periodId, $calcId, $err) = $this->addPeriodCalc($dateFirst, $periodType, $calcTypeCode);
                /* put result data into context */
                if (!$err) {
                    $this->populateResponse($result, $periodId, $calcId);
                }
            }
        } else {
            $periodId = $periodLast[QBGetCalc::A_PERIOD_ID];
            $calcId = $periodLast[QBGetCalc::A_CALC_ID];
            $calcState = $periodLast[QBGetCalc::A_CALC_STATE];
            $this->logger->info("There is registered period (#$periodId) and related calculation "
                . "(#$calcId:$calcState) for type $calcTypeCode.");
            if ($calcState != Cfg::CALC_STATE_COMPLETE) {
                /* calculation is not complete for the period */
                $result->setErrorCode(AResponse::ERR_CALC_NOT_COMPLETE);
            } else {
                /* there is complete calculation for the last period, start new period if it is possible */
                $periodEnd = $periodLast[QBGetCalc::A_DS_END];
                $dateNext = $this->hlpPeriod->getTimestampNextFrom($periodEnd, $periodType);
                list($periodId, $calcId, $err) = $this->addPeriodCalc($dateNext, $periodType, $calcTypeCode);
                /* put result data into context */
                if (!$err) {
                    $this->populateResponse($result, $periodId, $calcId);
                }
            }
        }
        $this->logger->info("'Get basis period calculation' processing is completed ($calcTypeCode).");
        return $result;
    }

    /**
     * Populate service response with result data if no error.
     *
     * @param \Praxigento\BonusBase\Api\Service\Period\Calc\Get\Basis\Response $response
     * @param int $periodId
     * @param int $calcId
     */
    private function populateResponse($response, $periodId, $calcId)
    {
        $periodData = $this->daoPeriod->getById($periodId);
        $calcData = $this->daoCalc->getById($calcId);
        $response->setPeriodData($periodData);
        $response->setCalcData($calcData);
        $response->setErrorCode(AResponse::ERR_NO_ERROR);
    }

    /**
     * Get applied date for the first transaction fir the given asset type.
     *
     * @param string $assetTypeCode
     * @return string|bool '2017-01-31 20:59:59' if data exists or 'false'.
     */
    private function queryFirstDate($assetTypeCode)
    {
        $query = $this->qbGetFirstDate->build();
        $bind = [
            $this->qbGetFirstDate::BND_ASSET_TYPE_CODE => $assetTypeCode
        ];
        $conn = $query->getConnection();
        $result = $conn->fetchOne($query, $bind);
        return $result;
    }

    /**
     * Perform query to get the last calculation by type.
     *
     * @param string $calcCode
     * @return array see \Praxigento\BonusBase\Repo\Query\Period\Calcs\GetLast\ByCalcTypeCode\Builder
     */
    private function queryLastPeriod($calcCode)
    {
        $query = $this->qbGetPeriod->build();
        /* modify query to get the last calculation by type code */
        $bindTypeCode = 'code';
        $whereType = $this->qbGetPeriod::AS_CALC_TYPE . '.' . ECalcType::A_CODE . "=:$bindTypeCode";
        $query->where($whereType);
        /* sort desc by calcId and limit results if there are more than one calculations for the period */
        $query->order($this->qbGetPeriod::AS_CALC . '.' . ECalc::A_ID . ' DESC');
        $query->limit(1);

        /* bind query parameters and get result set */
        $bind = [$bindTypeCode => $calcCode];
        $conn = $query->getConnection();
        $result = $conn->fetchRow($query, $bind);
        return $result;
    }
}
