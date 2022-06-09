<?php
    $user = $_GET['user'];
    $pass = $_GET['pass'];

    if(!isset($user) || !isset($pass)){
        die('No request');
    }

    $ldap = [
        'timeout' => 20,
        'host' => '172.24.232.140',
        'rdn' => 'CLAROCO\\' . $user,
        'pass' => $pass
    ];
    $host = $ldap["host"];
    $ldapport = 389;

    $ldapconn = ldap_connect($host, $ldapport)  or die("Fallo conexion con LDPA");

    ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);

    if ($ldapconn) {
        /* Realiza la autenticacion */
        $ldapbind = ldap_bind($ldapconn, $ldap["rdn"], $ldap["pass"]);
        
        var_dump($ldapbind);
    }