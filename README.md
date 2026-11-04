# Symfony Messenger & Scheduler com RabbitMQ

O projeto Symfony está em `skeleton/`. O Scheduler publica uma mensagem diária às 08:00 UTC, o worker `scheduler_default` encaminha essa mensagem para a fila RabbitMQ `async`, e outro worker executa o handler.

## Iniciar

Com Docker e Docker Compose instalados, execute na raiz do repositório:

```bash
docker compose up -d --build
```

O painel RabbitMQ estará disponível em `http://localhost:15672` (usuário e senha locais: `scheduler` / `scheduler`). Os workers do Scheduler e da fila são iniciados e supervisionados separadamente. Consulte a execução com:

```bash
docker compose logs -f php
docker compose exec php php bin/console debug:scheduler
```

O horário de execução usa explicitamente UTC. As credenciais do Compose são apenas para desenvolvimento local; altere-as antes de expor ou usar o serviço em produção.

## Testes

Os testes usam um transporte em memória e não precisam aguardar o horário agendado nem conectar ao RabbitMQ:

```bash
docker compose run --rm --no-deps php ./vendor/bin/phpunit
```

`SendDailySummaryHandler` registra que recebeu a mensagem para tornar o fluxo demonstrável. A geração e entrega real de um resumo ainda precisa ser implementada com os dados e destinatários da aplicação.

## Aviso

Este repositório foi criado exclusivamente para fins de estudo e demonstração. Caso deseje realizar a implementação em um ambiente de produção, consulte a [documentação oficial](https://symfony.com/doc/current/scheduler.html) para obter as diretrizes corretas e práticas de segurança.