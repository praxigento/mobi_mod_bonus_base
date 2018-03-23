<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\BonusBase\Service\Period;

class Call
    extends \Praxigento\Core\App\Service\Base\Call
    implements \Praxigento\BonusBase\Service\IPeriod
{
    /** @var \Praxigento\Core\Api\Helper\Date */
    protected $hlpDate;
    /** @var  \Praxigento\Core\Api\Helper\Period */
    protected $hlpPeriod;
    /** @var \Praxigento\Core\Api\App\Logger\Main */
    protected $logger;
    /** @var  \Praxigento\Core\Api\App\Repo\Transaction\Manager */
    protected $manTrans;
    /** @var \Praxigento\BonusBase\Repo\Dao\Calculation */
    protected $daoCalc;
    /** @var \Praxigento\BonusBase\Repo\Dao\Period */
    protected $daoPeriod;
    /** @var \Praxigento\BonusBase\Repo\Service\IModule */
    protected $daoService;
    /** @var \Praxigento\BonusBase\Repo\Dao\Type\Calc */
    protected $daoTypeCalc;
    /** @var  \Praxigento\BonusBase\Service\Period\Sub\Depended */
    protected $subDepended;
    /** @var \Praxigento\BonusBase\Service\Period\Sub\PvBased */
    protected $subPvBased;

    public function __construct(
        \Praxigento\Core\Api\App\Logger\Main $logger,
        \Magento\Framework\ObjectManagerInterface $manObj,
        \Praxigento\Core\Api\App\Repo\Transaction\Manager $manTrans,
        \Praxigento\BonusBase\Repo\Dao\Calculation $daoCalc,
        \Praxigento\BonusBase\Repo\Dao\Period $daoPeriod,
        \Praxigento\BonusBase\Repo\Dao\Type\Calc $daoTypeCalc,
        \Praxigento\BonusBase\Repo\Service\IModule $daoService,
        \Praxigento\Core\Api\Helper\Period $hlpPeriod,
        \Praxigento\Core\Api\Helper\Date $hlpDate,
        \Praxigento\BonusBase\Service\Period\Sub\Depended $subDepended,
        \Praxigento\BonusBase\Service\Period\Sub\PvBased $subPvBased
    ) {
        parent::__construct($logger, $manObj);
        $this->manTrans = $manTrans;
        $this->daoCalc = $daoCalc;
        $this->daoPeriod = $daoPeriod;
        $this->daoTypeCalc = $daoTypeCalc;
        $this->daoService = $daoService;
        $this->hlpPeriod = $hlpPeriod;
        $this->hlpDate = $hlpDate;
        $this->subDepended = $subDepended;
        $this->subPvBased = $subPvBased;
    }

    public function getLatest(Request\GetLatest $request)
    {
        $result = new Response\GetLatest();
        $calcTypeId = $request->getCalcTypeId();
        $calcTypeCode = $request->getCalcTypeCode();
        $msgParams = is_null($calcTypeId) ? "type code '$calcTypeCode'" : "type ID #$calcTypeId";
        $this->logger->info("'Get latest calculation period' operation is started with $msgParams in bonus base module.");
        if (is_null($calcTypeId)) {
            /* get calculation type ID by type code */
            $calcTypeId = $this->daoTypeCalc->getIdByCode($calcTypeCode);
            $this->logger->info("There is only calculation type code ($calcTypeCode) in request, calculation type id = $calcTypeId.");
        }
        $periodLatest = $this->daoService->getLastPeriodByCalcType($calcTypeId);
        if ($periodLatest) {
            $result->setPeriodData($periodLatest);
            /* add period calculations to result set */
            $periodId = $periodLatest->getId();
            $calcLatest = $this->daoService->getLastCalcForPeriodById($periodId);
            $result->setCalcData($calcLatest);
        }
        $result->markSucceed();
        $this->logger->info("'Get latest calculation period' operation is completed in bonus base module.");
        return $result;
    }
}