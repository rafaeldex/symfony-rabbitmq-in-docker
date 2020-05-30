## Symfony producer sending messages to symfony consumer using rabbitmq in Docker network

The producer can send messages using queue or from exchanges routed or not
The consumer can receive messages from queue and from exchanges routed or not

There are two different mysql databases. One for producer and other for consumer
There are two nginx server too

The consumer saves the received messages in mysql-consumer databases and we can check the databse using phpmyadmin

All in docker
