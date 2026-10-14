# Build em ambiente de desenvolvimento

Para subir a aplicação orquestrada localmente
```text
docker compose -f docker-compose.yml -f docker-compose.dev.yml up -d --build
```

# Diagrama da aplicação
Diagrama da aplicação: https://gitdiagram.com/geovanecodder/test-amar
Clique em "regenerate".

### Obs
A mesma planilha nunca será upada duas vezes, há uma parte do código que cria uma impressão digital
e caso SHA-256 e caso o usuário logado tentar fazer o upload da última planilha que ele já havia feito
o sistema não faz nada.
Os testes unitários serão executados pelo GitHub Actions somente em ambiente de develop(usando o sqlite).

## Para realizar o teste de notificação, pause o a fila:
```text
docker compose stop queue
```
Faça o upload de uma planilha qualquer, observe que o progress bar ficará em loop, o cliente poderá sair
da sessão upload normalmente, então suba novamente o container onde tem o work de processamento de filas

```text
docker compose start queue
```
A fila deverá ser processada e um toast deverá ser exibido na tela.

## Documentação dos enpoints backend
./docs/openapi.yaml