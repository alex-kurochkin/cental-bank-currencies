<?php
declare(strict_types=1);

namespace api\controllers\actions\currencies;

use api\controllers\actions\currencies\dtos\CurrencyListDto;
use api\controllers\actions\currencies\params\CurrencyListParams;
use api\models\currency\Currency;
use api\models\currency\services\CurrencyService;
use common\controllers\dtos\ObjectResponseDto;
use common\domain\mappers\dto\DtoMapper;
use common\domain\utils\ErrorMessageBuilder;
use yii\base\Action;
use yii\base\Controller;
use yii\web\BadRequestHttpException;

class ListAction extends Action
{

    private CurrencyService $currencyService;

    private DtoMapper $currencyMapper;

    public function __construct(
        $id,
        Controller $controller,
        CurrencyService $currencyService,
        $config = []
    ) {
        $this->currencyService = $currencyService;
        $this->currencyMapper = new DtoMapper(CurrencyListDto::class, CurrencyListDto::MAPPING, Currency::class);
        parent::__construct($id, $controller, $config);
    }

    public function run(): ObjectResponseDto
    {
        $get = $this->controller->getRequest()->get();

        $params = new CurrencyListParams();
        $params->load($get);
        if (!$params->validate()) {
            throw new BadRequestHttpException(ErrorMessageBuilder::build($params->errors));
        }

        $from = new \DateTimeImmutable($params->from);
        $to = new \DateTimeImmutable($params->to);

        $rates = $this->currencyService->findManyRatesByDateFromAndTo($from, $to);

        $dtos = [];
        if ($rates) {
            $dtos = $this->currencyMapper->toManyDtos($rates);
        }

        return new ObjectResponseDto($dtos);
    }
}
