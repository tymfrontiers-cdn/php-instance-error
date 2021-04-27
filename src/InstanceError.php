<?php
namespace TymFrontiers;

class InstanceError{

  private $_errors;
  private $_override=false;
  public $errors=[];

  function __construct($instance = '', $override=false){
    $this->_override = $override;
    if( \property_exists($instance, 'errors') ) $this->_errors = $instance->errors;
  }

  public function log( string $reference='',string $to_file=''){
    $errors = !empty($reference) ? $this->get($reference) : $this->get();

    if( !empty($reference) ){
      if( !\array_key_exists($reference,$this->_errors) ){
        $this->errors['log'][] = [256,3,"Errors does not have given key: '{$reference}'",__FILE__,__LINE__];
        return false;
      }
      foreach($this->get($reference) as $err){
        ( new ErrorLog($err[0],$reference,$err[2],$err[3],$err[4]) )->record($to_file);
      }
    }else{
      foreach($this->get() as $key=>$errors ){
        foreach($errors as $err){
          ( new ErrorLog($err[0],$key,$err[2],$err[3],$err[4]) )->record($to_file);
        }
      }
    }
  }
  public function get(string $reference='', bool $err_text=false){
    global $session;
    $rank = ( $session instanceof Session ) ? $session->access_rank() : 0;
    $return = [];
    if( !empty($reference) ){
      if( \array_key_exists($reference,$this->_errors) ){

        foreach ($this->_errors[$reference] as $err) {
          if( \is_bool($this->_override) && !(bool)$this->_override ){
            if( $rank >= $err[0] ) $return[] = $err_text ? $err[2] : $err;
          }elseif( \is_bool($this->_override) && (bool)$this->_override ){
            $return[] = $err_text ? $err[2] : $err;
          }else{
            if( \is_int($this->_override) ){
              $rank = (int)$this->_override;
              if( $rank >= $err[0] ) $return[] = $err_text ? $err[2] : $err;
            }
          }

        }
      }else{
        // $this->errors['get'][] = [256,3,"Errors does not have given key: '{$reference}'",__FILE__,__LINE__];
        return [];
      }
    }else{
      foreach($this->_errors as $key=>$errors){
        foreach($errors as $err){
          if( \is_bool($this->_override) && !(bool)$this->_override ){
            if( $rank >= $err[0] ) $return[$key][] = $err_text ? $err[2] : $err;
          }elseif( \is_bool($this->_override) && (bool)$this->_override ){
            $return[$key][] = $err_text ? $err[2] : $err;
          }else{
            if( \is_int($this->_override) ){
              $rank = (int)$this->_override;
              if( $rank >= $err[0] ) $return[$key][] = $err_text ? $err[2] : $err;
            }
          }
        }
      }
    }
    return $return;
  }
  public function put(string $ref,array $val){
    if( !empty($this->_errors) ) $this->_errors[$ref][] = $val;
  }

}
