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
    $type  = (isset($update['originalRequest']['source']) and $update['originalRequest']['source'] == 'google') ? 'simple_response' : 0;
    if (isset($return['data']['lamp'])) {

        if($update['result']['parameters']['acao'] == 'status'){
            $status = ($return['data']['lamp'] == "true") ? "LIGADA" : "DESLIGADA";
            $speech = "A lâmpada está {$status} nesse momento";
        }else if($update['result']['parameters']['acao'] == 'turn-on'){
            $speech = ($return['data']['lamp'] == "true") ? "Okay, acabei de ligar a lampada." : "Oops, não consegui ligar a lâmpada.";
        }else if($update['result']['parameters']['acao'] == 'turn-off'){
            $speech = ($return['data']['lamp'] == "false") ? "Okay, acabei de desligar a lampada." : "Oops, não consegui desligar a lâmpada.";
         }

        $array = array(
            'type' => $type,
            'speech' => $speech,
            'textToSpeech'=> $speech,
            'displayText'=> $speech
        );
        if(isset($update['originalRequest']['source'])){
            $array['platform'] = $update['originalRequest']['source'];
        }
        $message[] = $array;


    } else {
        $array = array(
            'type' => $type,
            'speech' => 'Desculpe, não consegui realizar a ação solicitada. Acredito que o servidor local esteja offline',
            'textToSpeech'=> 'Desculpe, não consegui realizar a ação solicitada. Acredito que o servidor local esteja offline',
            'displayText'=> 'Desculpe, não consegui realizar a ação solicitada. Acredito que o servidor local esteja offline',
        );
        if(isset($update['originalRequest']['source'])){
            $array['platform'] = $update['originalRequest']['source'];
        }
        $message[] = $array;
    }

    $array = array(
        'type' => $type,
        'speech' => 'Gostaria de realizar outra ação ?',
        'textToSpeech'=> 'Gostaria de realizar outra ação ?',
        'displayText'=> 'Gostaria de realizar outra ação ?',
    );
    
    if(isset($update['originalRequest']['source'])){
        $array['platform'] = $update['originalRequest']['source'];
    }
    
    $message[] = $array;
    
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
