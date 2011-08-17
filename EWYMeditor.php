<?php
/**
 * EWYMeditor class file.
 * 
 * @author Andrius Marcinkevicius <andrew.web@ifdattic.com>
 * @copyright Copyright &copy; 2011 Andrius Marcinkevicius
 * @license Licensed under MIT license. http://ifdattic.com/MIT-license.txt
 * @version 1.0
 */

/**
 * EWYMeditor adds a WYSIWYM (What You See Is What You Mean) XHTML editor.
 * 
 * @author Andrius Marcinkevicius <andrew.web@ifdattic.com>
 */
class EWYMeditor extends CInputWidget
{
  /**
   * @var array JavaScript options that should be passed to the plugin.
   */
  public $options = array();
  
  /**
   * @var array the plugins which should be added to editor.
   */
  public $plugins = array();
  
  /**
   * @var string apply wymeditor plugin to these elements.
   */
  public $target = null;
  
  /**
   * Add WYMeditor to the page.
   */
  public function run()
  {
    // Add textarea to the page  
    if( $this->target === null )
    {
      list( $name, $id ) = $this->resolveNameID();
      
      if( $this->hasModel() )
        echo CHtml::activeTextArea( $this->model, $this->attribute, $this->htmlOptions );
      else
        echo CHtml::textArea( $name, $this->value, $this->htmlOptions );
    }
    
    // Publish extension assets
    $assets = Yii::app()->getAssetManager()->publish( Yii::getPathOfAlias(
      'ext.EWYMeditor' ) . '/assets' );
    $cs = Yii::app()->getClientScript();
    $cs->registerScriptFile( $assets . '/jquery.wymeditor.js', 
      CClientScript::POS_END );
    
    // Add the plugins to editor
    if( $this->plugins !== array() )
    {
      $this->_addPlugins( $cs, $assets );
    }
    
    $options = CJavaScript::encode( $this->options );
    
    if( $this->target === null )
    {
      $cs->registerScript( 'wym', "jQuery('#{$id}').wymeditor({$options});" );
    }
    else
    {
      $cs->registerScript( 'wym', "jQuery('{$this->target}').wymeditor({$options});" );
    }
  }
  
  /**
   * Add plugins to the editor.
   * @var CClientScript the client script object.
   * @var string the path to the assets. 
   */
  private function _addPlugins( $cs, $assets )
  {
    // Available plugins array
    $plugins = array(
      'hovertools' => array(
        'file' => '/plugins/hovertools/jquery.wymeditor.hovertools.js',
        'init' => 'wym.hovertools();' ),
      'fullscreen' => array(
        'file' => '/plugins/fullscreen/jquery.wymeditor.fullscreen.js',
        'init' => 'wym.fullscreen();' ),
      'tidy' => array(
        'file' => '/plugins/tidy/jquery.wymeditor.tidy.js',
        'init' => 'var wymtidy = wym.tidy();wymtidy.init();' ),
      'resizable' => array(
        'file' => '/plugins/resizable/jquery.wymeditor.resizable.js',
        'init' => 'wym.resizable();' ),
    );
    
    // Replacement for 'postInit' option
    $postInit = array();
    
    // If string provided, convert it to an array
    if( !is_array( $this->plugins ) )
    {
      $this->plugins = explode( ',', $this->plugins );
      $this->plugins = array_map( 'trim', $this->plugins );
    }
    
    // Add all available plugins
    foreach( $this->plugins as $plugin )
    {
      if( isset( $plugins[$plugin] ) )
      {
        $cs->registerScriptFile( $assets . $plugins[$plugin]['file'],
          CClientScript::POS_END );
        $postInit[] = $plugins[$plugin]['init']; 
      }
    }
    
    // Replace 'postInit' option if user hasn't provided a custom one
    if( !isset( $this->options['postInit'] ) )
    {
      $this->options['postInit'] = "js:function(wym){"
        . implode( '', $postInit ) . "}";
    }
  }
}
?>