<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2018
 */

namespace Praxigento\BonusBase\Service\Period\Calc\Get;

use Praxigento\BonusBase\Api\Service\Period\Calc\Get\Last\Request as ARequest;
use Praxigento\BonusBase\Api\Service\Period\Calc\Get\Last\Response as AResponse;
use Praxigento\BonusBase\Config as Cfg;
use Praxigento\BonusBase\Repo\Data\Calculation as ECalc;
use Praxigento\BonusBase\Repo\Data\Period as EPeriod;
use Praxigento\BonusBase\Repo\Data\Type\Calc as ETypeCalc;
use Praxigento\BonusBase\Repo\Query\Period\Calcs\Builder as QCalcs;

/**
 * Get data for the last complete calculation.
 */
class Last
    implements \Praxigento\BonusBase\Api\Service\Period\Calc\Get\Last
{
    /** @var \Praxigento\BonusBase\Repo\Query\Period\Calcs\Builder */
    private $qCalcs;

    public function __construct(
        \Praxigento\BonusBase\Repo\Query\Period\Calcs\Builder $qCalcs
    ) {
        $this->qCalcs = $qCalcs;
    }


    public function exec($request)
    {
        /** define local working data */
        assert($request instanceof ARequest);
        $result = new AResponse();
        $calcTypeCode = $request->getCalcTypeCode();

        /** perform processing */
        $query = $this->qCalcs->build();
        $conn = $query->getConnection();
        /* tune base query */
        $bndByState = 'calcState';
        $bndByCode = 'calcCode';

        /* WHERE */
        $byState = QCalcs::AS_CALC . '.' . ECalc::A_STATE . "=:$bndByState";
        $byCode = QCalcs::AS_CALC_TYPE . '.' . ETypeCalc::A_CODE . "=:$bndByCode";
        $where = "($byState) AND ($byCode)";
        $query->where($where);

        /* ORDER */
        $byDateEnded = QCalcs::A_DS_END . ' DESC';
        $query->order($byDateEnded);

        /* LIMIT */
        $query->limit(1);

        /* run query and fetch data */
        $bind = [
            $bndByState => Cfg::CALC_STATE_COMPLETE,
            $bndByCode => $calcTypeCode
        ];
        $rs = $conn->fetchAll($query, $bind);
        if (
            is_array($rs) &&
            count($rs)
        ) {
            $entry = reset($rs);
            $calc = new ECalc();
            $calc->setId($entry[QCalcs::A_CALC_ID]);
            $calc->setPeriodId($entry[QCalcs::A_PERIOD_ID]);
            $calc->setState($entry[QCalcs::A_CALC_STATE]);
            $calc->setDateStarted($entry[QCalcs::A_DATE_STARTED]);
            $calc->setDateEnded($entry[QCalcs::A_DATE_ENDED]);
            $result->setCalc($calc);

            $period = new EPeriod();
            $period->setId($entry[QCalcs::A_PERIOD_ID]);
            $period->setCalcTypeId($entry[QCalcs::A_CALC_TYPE_ID]);
            $period->setDstampBegin($entry[QCalcs::A_DS_BEGIN]);
            $period->setDstampEnd($entry[QCalcs::A_DS_BEGIN]);
            $result->setPeriod($period);

            $result->markSucceed();
        }

        /** compose result */
        return $result;
    }

}