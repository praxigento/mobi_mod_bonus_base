<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Service\Period\Calc\Get;

use Praxigento\BonusBase\Api\Service\Period\Calc\Get\All\Request as ARequest;
use Praxigento\BonusBase\Api\Service\Period\Calc\Get\All\Response as AResponse;
use Praxigento\BonusBase\Repo\Data\Calculation as ECalc;
use Praxigento\BonusBase\Repo\Data\Period as EPeriod;
use Praxigento\BonusBase\Repo\Query\Period\Calcs\Get\Builder as QBCalcsGet;
use Praxigento\Core\Api\Helper\Period as HPeriod;

class All
    implements \Praxigento\BonusBase\Api\Service\Period\Calc\Get\All
{
    /** @var \Praxigento\Core\Api\Helper\Period */
    private $hlpPeriod;
    /** @var \Praxigento\BonusBase\Repo\Query\Period\Calcs\Get\Builder */
    private $qbCalcsGet;

    public function __construct(
        \Praxigento\BonusBase\Repo\Query\Period\Calcs\Get\Builder $qbCalcsGet,
        \Praxigento\Core\Api\Helper\Period $hlpPeriod
    ) {
        $this->qbCalcsGet = $qbCalcsGet;
        $this->hlpPeriod = $hlpPeriod;
    }

    public function exec($request)
    {
        /** define local working data */
        assert($request instanceof ARequest);
        $period = $request->getPeriod();
        if (!$period) {
            $period = $this->hlpPeriod->getPeriodCurrent(null, 0, HPeriod::TYPE_MONTH);
        }
        /** perform processing */

        $dsBegin = $this->hlpPeriod->getPeriodFirstDate($period);
        $dsEnd = $this->hlpPeriod->getPeriodLastDate($period);
        $query = $this->qbCalcsGet->build();
        $conn = $query->getConnection();
        $bind = [
            QBCalcsGet::BND_DATE_BEGIN => $dsBegin,
            QBCalcsGet::BND_DATE_END => $dsEnd,
            QBCalcsGet::BND_STATE => \Praxigento\BonusBase\Config::CALC_STATE_COMPLETE
        ];
        $rs = $conn->fetchAll($query, $bind);
        $periodData = null;
        $calcs = [];
        if (is_array($rs)) {
            foreach ($rs as $one) {
                $calcData = new ECalc();
                $calcData->setId($one[QBCalcsGet::A_CALC_ID]);
                $calcData->setState($one[QBCalcsGet::A_CALC_STATE]);
                $calcData->setDateStarted($one[QBCalcsGet::A_CALC_DATE_START]);
                $calcData->setDateEnded($one[QBCalcsGet::A_CALC_DATE_END]);
                $calcData->setPeriodId($one[QBCalcsGet::A_PERIOD_ID]);
                $key = $one[QBCalcsGet::A_CALC_TYPE_CODE];
                $calcs[$key] = $calcData;
            }
            /* get period data from the first array item */
            $periodData = new EPeriod();
            $one = reset($rs);
            if (is_array($one)) {
                $periodData->setId($one[QBCalcsGet::A_PERIOD_ID]);
                $periodData->setDstampBegin($one[QBCalcsGet::A_PERIOD_BEGIN]);
                $periodData->setDstampEnd($one[QBCalcsGet::A_PERIOD_END]);
            }
        }

        /** compose result */
        $result = new AResponse();
        $result->setPeriod($periodData);
        $result->setCalcs($calcs);
        $result->markSucceed();
        return $result;
    }

}