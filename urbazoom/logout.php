<?php
require __DIR__ . '/config.php';

fazer_logout();
ir_para('login.php?tipo=success&msg=Voce+saiu+do+sistema');
