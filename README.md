#AMQP Buzzer - CLI-утилита для работы с очередями

###Установка:
Требования:
- PHP > 7.2
- Docker

Установить зависимости:
```
make composer install
```

Запустить Rabbit:
```
make rabbit
```
После чего брокер будет доступен по адресу localhost:15672 с логином и паролем guest

###Использование:
Опубликовать сообщение "Hello World" в очередь "test-queue"
```
amqp-buzzer publish test-queue --message="Hello World"
```

Прослушивать очередь "test-queue"
```
amqp-buzzer listen test-queue
```

TODO:
- Добавить конфигурацию
- Докрутить PHP-doc
- Добавить тесты
- Добавить github CI
