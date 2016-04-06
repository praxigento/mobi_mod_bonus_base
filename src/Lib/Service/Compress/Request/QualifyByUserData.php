<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\Bonus\Base\Lib\Service\Compress\Request;

use Praxigento\Bonus\Base\Lib\Tool\IQualifyUser;

/**
 * @method int getCalcId()
 * @method void setCalcId(int $data)
 * @method array getFlatTree()
 * @method void setFlatTree(array $data)
 * @method IQualifyUser getQualifier()
 * @method void setQualifier(IQualifyUser $obj)
 * @method bool getSkipTreeExpand() 'true' if FlatTree has depth & path info
 * @method void setSkipTreeExpand(bool $data)
 */
class QualifyByUserData extends \Praxigento\Core\Service\Base\Request {

}