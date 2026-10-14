## Build em ambiente de desenvolvimento

Para subir a aplicação orquestrada localmente: docker compose -f docker-compose.yml -f docker-compose.dev.yml up -d --build

## Diagrama da aplicação
Diagrama da aplicação: https://gitdiagram.com/geovanecodder/test-amar

### Obs
A mesma planilha nunca será upada duas vezes, há uma parte do código que cria uma impressão digital
e caso SHA-256 e caso o usuário logado tentar fazer o upload da última planilha que ele já havia feito
o sistema não faz nada.

Para realizar o teste de notificação, pause o a fila:
- docker compose stop queue

Faça o upload de uma planilha qualquer, observe que o progress bar ficará em loop, o cliente poderá sair
da sessão upload normalmente, então suba novamente o container onde tem o work de processamento de filas

- docker compose start queue

A fila deverá ser processada e um toast deverá ser exibido na tela.