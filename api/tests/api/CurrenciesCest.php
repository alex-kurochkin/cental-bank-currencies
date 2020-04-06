<?php
declare(strict_types=1);

namespace tests\api;

use Codeception\Util\HttpCode;
use api\tests\ApiTester;
use common\fixtures\currencies\CurrencyArFixture;
use \Exception;

/**
 * Class CurrenciesApiCest
 * @package tests\api
 *
 * PHP server and CLI timezone must be UTC
 */
class CurrenciesApiCest
{

    public function _fixtures(): array
    {
        return [
            'Currency' => CurrencyArFixture::class,
        ];
    }

    /**
     * @param ApiTester $I
     * @throws Exception
     * @group CurrenciesAPI
     */
    public function testCurrenciesOneDayGetApi(ApiTester $I): void
    {
        $I->wantToTest('to GET one day currencies list');
        $I->amBearerAuthenticated('tester-token');

        $I->sendGET('currencies/2020-01-01/2020-01-01');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson(
            [
                'data' => [
                    [
                        'id' => 1,
                        'valuteId' => 'R00001',
                        'numCode' => 1,
                        'charCode' => 'FM1',
                        'name' => 'Free money 1',
                        'nominal' => 1,
                        'value' => 1.2345,
                        'date' => '2020-01-01',
                    ],
                    [
                        'id' => 2,
                        'valuteId' => 'R00002',
                        'numCode' => 2,
                        'charCode' => 'FM2',
                        'name' => 'Free money 2',
                        'nominal' => 1,
                        'value' => 1.2345,
                        'date' => '2020-01-01',
                    ],
                    [
                        'id' => 3,
                        'valuteId' => 'R00003',
                        'numCode' => 3,
                        'charCode' => 'FM3',
                        'name' => 'Free money 3',
                        'nominal' => 1,
                        'value' => 1.2345,
                        'date' => '2020-01-01',
                    ],
                ],
                'result' => 'success',
                'message' => null,
            ]
        );

        $currencies = $I->grabDataFromResponseByJsonPath('$.data')[0];
        $I->assertCount(3, $currencies);
    }

    /**
     * @param ApiTester $I
     * @throws Exception
     * @group CurrenciesAPI
     */
    public function testCurrenciesTwoDaysGetApi(ApiTester $I): void
    {
        $I->wantToTest('to GET tow days currencies list');
        $I->amBearerAuthenticated('tester-token');

        $I->sendGET('currencies/2020-01-01/2020-01-02');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson(
            [
                'data' => [
                    [
                        'id' => 1,
                        'valuteId' => 'R00001',
                        'numCode' => 1,
                        'charCode' => 'FM1',
                        'name' => 'Free money 1',
                        'nominal' => 1,
                        'value' => 1.2345,
                        'date' => '2020-01-01',
                    ],
                    [
                        'id' => 2,
                        'valuteId' => 'R00002',
                        'numCode' => 2,
                        'charCode' => 'FM2',
                        'name' => 'Free money 2',
                        'nominal' => 1,
                        'value' => 1.2345,
                        'date' => '2020-01-01',
                    ],
                    [
                        'id' => 3,
                        'valuteId' => 'R00003',
                        'numCode' => 3,
                        'charCode' => 'FM3',
                        'name' => 'Free money 3',
                        'nominal' => 1,
                        'value' => 1.2345,
                        'date' => '2020-01-01',
                    ],
                    [
                        'id' => 4,
                        'valuteId' => 'R00001',
                        'numCode' => 1,
                        'charCode' => 'FM1',
                        'name' => 'Free money 1',
                        'nominal' => 1,
                        'value' => 2.2345,
                        'date' => '2020-01-02',
                    ],
                    [
                        'id' => 5,
                        'valuteId' => 'R00002',
                        'numCode' => 2,
                        'charCode' => 'FM2',
                        'name' => 'Free money 2',
                        'nominal' => 1,
                        'value' => 2.2345,
                        'date' => '2020-01-02',
                    ],
                    [
                        'id' => 6,
                        'valuteId' => 'R00003',
                        'numCode' => 3,
                        'charCode' => 'FM3',
                        'name' => 'Free money 3',
                        'nominal' => 1,
                        'value' => 2.2345,
                        'date' => '2020-01-02',
                    ],
                ],
                'result' => 'success',
                'message' => null,
            ]
        );

        $currencies = $I->grabDataFromResponseByJsonPath('$.data')[0];
        $I->assertCount(6, $currencies);
    }

    /**
     * @param ApiTester $I
     * @throws Exception
     * @group CurrenciesAPI
     */
    public function testCurrenciesEmptyResultGetApi(ApiTester $I): void
    {
        $I->wantToTest('to GET empty list');
        $I->amBearerAuthenticated('tester-token');

        $I->sendGET('currencies/2020-01-11/2020-01-11'); // <--- No data in fixtures present
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson(
            [
                'data' => [],
                'result' => 'success',
                'message' => null,
            ]
        );

        $currencies = $I->grabDataFromResponseByJsonPath('$.data')[0];
        $I->assertCount(0, $currencies);
    }

    /**
     * @param ApiTester $I
     * @throws Exception
     * @group CurrenciesAPI
     */
    public function testCurrenciesWrongDaysResultGetApi(ApiTester $I): void
    {
        $I->wantToTest('to To less then From');
        $I->amBearerAuthenticated('tester-token');

        $I->sendGET('currencies/2020-01-02/2020-01-01');
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson(
            [
                'name' => 'Bad Request',
                'message' => 'From must be less than or equal to "To".',
                'code' => 0,
                'status' => 400,
            ]
        );
    }

    /**
     * @param ApiTester $I
     * @throws Exception
     * @group CurrenciesAPI
     */
    public function testCurrenciesWrongFromDayResultGetApi(ApiTester $I): void
    {
        $I->wantToTest('to wrong From format');
        $I->amBearerAuthenticated('tester-token');

        $I->sendGET('currencies/2020-01-32/2020-01-01');
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson(
            [
                'name' => 'Bad Request',
                'message' => 'The format of From is invalid.',
                'code' => 0,
                'status' => 400,
            ]
        );
    }

    /**
     * @param ApiTester $I
     * @throws Exception
     * @group CurrenciesAPI
     */
    public function testCurrenciesWrongToDayResultGetApi(ApiTester $I): void
    {
        $I->wantToTest('to wrong To format');
        $I->amBearerAuthenticated('tester-token');

        $I->sendGET('currencies/2020-01-01/2020-01-51');
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson(
            [
                'name' => 'Bad Request',
                'message' => 'The format of To is invalid.',
                'code' => 0,
                'status' => 400,
            ]
        );
    }

    /**
     * @param ApiTester $I
     * @throws Exception
     * @group CurrenciesAPI
     */
    public function testCurrenciesMissedToDayResultGetApi(ApiTester $I): void
    {
        $I->wantToTest('to missed To');
        $I->amBearerAuthenticated('tester-token');

        $I->sendGET('currencies/2020-01-01/');
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    /**
     * @param ApiTester $I
     * @throws Exception
     * @group CurrenciesAPI
     */
    public function testCurrenciesMissedFromDayResultGetApi(ApiTester $I): void
    {
        $I->wantToTest('to missed From');
        $I->amBearerAuthenticated('tester-token');

        $I->sendGET('currencies//2020-01-02');
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }
}
