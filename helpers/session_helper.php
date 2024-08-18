<?php
  session_start();

  function flash($name, $message = '') {
    if (!empty($message)) {
      $_SESSION[$name] = $message;
    } elseif (isset($_SESSION[$name])) {
      echo '<p>' . $_SESSION[$name] . '</p>';
      unset($_SESSION[$name]);
    }
  }

?>
