/*
   IMPORTANDO AS BIBLIOTECAS
*/
#include <SPI.h>
#include <Ethernet.h>

/*
   DEFININDO O PINO DO RELAY
*/
int relaySensor = 2;

/*
    CONFIGURANDO O IP QUE A ETHERNET SHIELD TERA
    (ALTERAR COM O DA SUA REDE)
*/
byte mac[] = {0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xED};
byte ip[] = {192, 168, 0, 23};
char buffer [128];
/*
   CRIANDO UM SEVIDOR WEB
*/
EthernetServer server(80);

/*
   INICIALIZANDO OS SERVICOS
*/
void setup() {
  Ethernet.begin(mac, ip);
  Serial.begin(9600);
  
  /*
     CONFIGURA O RELAY
     INICIA A LAMPADA APAGADA
  */
  pinMode(relaySensor, OUTPUT);
  digitalWrite(relaySensor, HIGH);

}

void loop() {
  /*
     TENTA PEGAR A CONEXAO COM O NAVEGADOR
  */
  
  EthernetClient client = server.available();

  /*
     VERIFICA SE EXISTE UM CLIENTE EM CONEXAO
  */
  if (client) {
    String request;
    String todo;
    boolean currentLineIsBlank = true;
    while (client.connected()) {
      /*
         VERIFICA SE OS DADOS ESTAO DISPONIVEIS PARA LEITURA
         (LEITURA DE 1 BYTE)
      */
      if (client.available()) {
        Serial.begin(9600);
        char c = client.read();
        /*
           CONCATENANDO TODA A REQUISICAO
        */
        request.concat(c);
        if (request.endsWith("/status")) {
          todo = "LAMP_STS";
        } else if (request.endsWith("/turn-on")) {
          todo = "LAMP_ON";
        } else if (request.endsWith("/turn-off")) {
          todo = "LAMP_OFF";
        }

        /*
           ULTIMA LINHA DA REQUISICAO É BRANCA E TERMINA COM O CARACTERE \n
           RESPONDE PARA O CLIENTE APENAS APÓS A ULTIMA LINHA RECEBIDA
        */
        if (c == '\n' && currentLineIsBlank) {

          /*
              CABECALHO PADRAO HTTP/JSON
          */
          client.println("HTTP/1.1 200 OK");
          client.println("Access-Control-Allow-Origin: *");
          client.println("Content-Type: application/json");
          client.println("Connection: close");
          client.println();

          /*
             MONTANDO O RETONO EM JSON
             (CONCATENADO)
          */
          if (todo == "LAMP_STS") {
            String s = (digitalRead(relaySensor) == LOW) ? "true" : "false";
            String p = "{\"data\": {\"lamp\": \""; p += s; p += "\"}}";
            client.println(p);
          } else if (todo == "LAMP_ON") {
            digitalWrite(relaySensor, LOW);
            String s =  "true";
            String p = "{\"data\": {\"lamp\": \""; p += s; p += "\"}}";
            client.println(p);
          } else if (todo == "LAMP_OFF") {
            digitalWrite(relaySensor, HIGH);
            String s =  "false";
            String p = "{\"data\": {\"lamp\": \""; p += s; p += "\"}}";
            client.println(p);
          }
          break;
        }
        /*
            TODA LINHA DE TEXTO RECEBIDA DO CLIENTE TERMINA COM OS CARACTERES \r\n
        */
        if (c == '\n') {
          /*
             ULTIMO CARACTERE DA LINHA DO TEXTO RECEBIDO
             INICIA UMA NOVA LINHA COM O CARACTERE LIDO
          */
          currentLineIsBlank = true;
        }
        else if (c != '\r') {
          /*
             UM CARACTERE DE TEXTO FOI RECEBIDO DO CLIENTE
          */
          currentLineIsBlank = false;
        }
      }
    }

    /*
       DELAY PARA O BROWSER RECEBER O TEXTO
    */
    delay(1);
    client.stop();

  }
}
