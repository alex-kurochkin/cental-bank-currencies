Демонстрационный код.

На базе Yii2 advanced

CORS ожидает доменое имя http://currencies.local
Настраивается в api/config/params.php

API аутентификация (Bearer) отключена.

JS обращается в локальное API на http://api-currencies.local
Установлено в JS скрипте frontend/web/js/currencies.js в объекте Currencies

Заполнение БД данными публичного API Ценрабанка.
php -f yii centrobank/load
Данные истории за 30 дней начиная с текущего. 

Немного тестов
./vendor/bin/codecept run -g CurrenciesAPI -- -c api