<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\Bonus\Base\Lib\Service;

use Praxigento\Bonus\Base\Lib\Service\Compress\Request\QualifyByUserData as QualifyByUserDataRequest;
use Praxigento\Bonus\Base\Lib\Service\Compress\Response\QualifyByUserData as QualifyByUserDataResponse;

interface ICompress {

    /**
     * @param QualifyByUserDataRequest $request
     *
     * @return QualifyByUserDataResponse
     */
    public function qualifyByUserData(QualifyByUserDataRequest $request);
}