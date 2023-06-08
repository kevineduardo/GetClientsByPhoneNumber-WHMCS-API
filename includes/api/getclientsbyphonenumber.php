<?php
// Incluir o arquivo init.php do WHMCS para ter acesso aos recursos do sistema
require_once __DIR__ . '/../../init.php';

use WHMCS\Database\Capsule;


if (!defined("WHMCS"))
    die("This file cannot be accessed directly");


function get_env($vars)
{
    $array = array('action' => array(), 'gid' => array());

    if (isset($vars['cmd'])) {
        //Local API mode
        $array['action'] = $vars['cmd'];
        $array['params'] = (object) $vars['apivalues1'];
        $array['adminuser'] = $vars['adminuser'];
    } else {
        //Post CURL mode
        $array['action'] = $vars['action'];
        unset($vars['_POST']['username']);
        unset($vars['_POST']['password']);
        unset($vars['_POST']['action']);
        $array['phonenumber'] = $vars['phonenumber'];
    }
    return (object) $array;
}

$post_fields = get_env(get_defined_vars());

// Verificar se o comando getclientsbyphonenumber foi chamado
if ($post_fields->action === 'GetClientsByPhoneNumber') {
    // Obter o número de telefone do parâmetro "phonenumber" enviado na requisição
    $phoneNumber = $post_fields->phonenumber;

    $clients = [];
    if (!empty($phoneNumber)) {
        $clients = localizaClientesPorNumeroTelefone($phoneNumber);
    }

    // Retornar a resposta em formato JSON
    $apiresults = array(
            'result' => 'success',
            'totalresults' => count($clients),
            'clients' => $clients
        );
}

/**
 * Função para localizar os clientes com base no número de telefone
 *
 * @param string $phoneNumber Número de telefone a ser pesquisado
 * @return array Lista de clientes encontrados
 */
function localizaClientesPorNumeroTelefone($phoneNumber) {
    $clientsList = Capsule::table('tblclients')->whereRaw("REGEXP_REPLACE(phonenumber, '[^0-9]', '') LIKE '%" . $phoneNumber . "%'")->get();

    // Retorno com dados
    return $clientsList;
}