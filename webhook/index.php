<?php
/*
 * PROCESSANDO A message 
 */
function processMessage($update) {
    if ($update["result"]["action"] == "lamp") {
        $array = action($update);
        sendMessage($array);
    }
}
/*
 * FUNÇÃO PARA ENVIAR A message
 */
function sendMessage($parameters) {
    echo json_encode($parameters);
}
/*
 * PEGANDO A REQUISIÇÃO
 */
$update_response = file_get_contents("php://input");
$update = json_decode($update_response, true);
if (isset($update["result"]["action"])) {
    processMessage($update);
}
/*
 * FUNÇÃO PARA EXECUTAR AS ACOES
 * NO SERVIDOR LOCAL COM ARDUINO
 */
function action($update = array()) {
    $message = array();
    $return = json_decode(file_get_contents('http://192.168.0.23/' . $update['result']['parameters']['acao']), true);
    if (isset($return['data']['lamp'])) {

        if($update['result']['parameters']['acao'] == 'status'){
            $status = ($return['data']['lamp'] == true) ? "LIGADA" : "DESLIGADA";
            $speech = "A lâmpada está {$status} nesse momento";
        }else if($update['result']['parameters']['acao'] == 'turn-on'){
            $speech = ($return['data']['lamp'] == true) ? "Okay, acabei de ligar a lampada." : "Oops, não consegui ligar a lâmpada.";
        }else if($update['result']['parameters']['acao'] == 'turn-on'){
            $speech = ($return['data']['lamp'] == false) ? "Okay, acabei de desligar a lampada." : "Oops, não consegui desligar a lâmpada.";
         }

        $message[] = array(
            'type' => 0,
            'speech' => $speech;
        );

    } else {
        $message[] = array(
            'type' => 0,
            'speech' => 'Desculpe, não consegui realizar a ação solicitada. Acredito que o servidor local esteja offline'
        );
    }
    $message[] = array(
        'type' => 0,
        'speech' => 'Gostaria de realizar outra ação ?',
    );
    return array(
        'source' => $update['result']['source'],
        'messages' => $message,
        'contextOut' => array(
            array(
                'name' => 'ctx-lampada',
                'lifespan' => 1,
                'parameters' => array()
            )
        )
    );
}
