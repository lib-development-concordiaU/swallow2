<?php
Class LDAPAuth {
    static function netnameLogin($netname_in,$password) {
        // ldap setup
        $ldap_host = "ldaps://v-ldap.concordia.ca";
        $ldap_dn = "OU=People,DC=concordia,DC=ca";
        $base_dn = "DC=concordia,DC=ca";
        $ldap_usr_dom = "@concordia.ca";
        $ldap = ldap_connect($ldap_host, 636);
        $bind_username = "lib-sduser";
        $bind_password = "C0nu1455";
    
        $ldap = ldap_connect($ldap_host, 636);
        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION,3);
        ldap_set_option($ldap, LDAP_OPT_REFERRALS,0);

        $netname = strtoupper(preg_replace("/[^0-9A-Za-z_]/", '', $netname_in));
            
        if (($netname=="")||($password == "")) {
            return FALSE;
        }	
        
        $bind = ldap_bind($ldap, $netname . $ldap_usr_dom, $password);
        
        if( !$bind || !isset($bind)) {
            return FALSE;
        }else{
            $results = ldap_search($ldap,$ldap_dn, "cn=" . $netname);
		    $data = ldap_get_entries($ldap, $results);
            if(isset($data[0]['mail'][0])){
                return ($data[0]['mail'][0]);
            }else{
                return false;
            }
        }
    }
}
    

?>