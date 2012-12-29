<?php
/**
 * Joomla FB Likes Plugin
 *
 * @version  1.0
 * @author Daniel Eliasson - joomla at stilero.com
 * @copyright  (C) 2012-dec-29 Stilero Webdesign http://www.stilero.com
 * @category Plugins
 * @license	GPLv2
 */

// no direct access
defined('_JEXEC') or die('Restricted access'); 

// import library dependencies
jimport('joomla.plugin.plugin');
define('FBLIKES_INCLUDES', JPATH_PLUGINS.'/content/fblikes/fblikes/includes/');
JLoader::register('FBCJArticle', FBLIKES_INCLUDES.'fbljarticle.php');
JLoader::register('FblSocialPlugin', FBLIKES_INCLUDES.'fblsocialplugin.php');
JLoader::register('FBLikes', FBLIKES_INCLUDES.'fblikes.php');
//JLoader::register('FBLikes', FBCOMMENTS_INCLUDES.'fblikes.php');

class plgContentFblikes extends JPlugin {
    
    private $classNames;
    private $Article;
    private $debug;
    private static $JArticleFile = 'fbljarticle.php';
    
    function plgContentFblikes ( &$subject, $config ) {
        parent::__construct( $subject, $config );
        $language = JFactory::getLanguage();
        $language->load('plg_content_fblikes', JPATH_ADMINISTRATOR, 'en-GB', true);
        $language->load('plg_content_fblikes', JPATH_ADMINISTRATOR, null, true);
        $this->classNames = array(
            'com_article'       =>  'FBLJArticle',
            'com_content'       =>  'FBLJArticle',
            'com_k2'            =>  'FBLK2Article',
            'com_zoo'           =>  'FBLZooArticle',
            'com_virtuemart'    =>  'FBLVMArticle'
        );
        $this->debug = FALSE;
    }
    
    private function loadClasses($article){
       $component = JRequest::getVar('option');
        if(array_key_exists($component, $this->classNames)){
            $className = $this->classNames[$component];
            JLoader::register( $className, FBLIKES_INCLUDES.self::$JArticleFile);
            $articleFactory = new $className($article);
            $this->Article = $articleFactory->getArticleObj();
            if($this->debug == true){
                JError::raiseNotice('0', 'Class; '.$className);
                JError::raiseNotice('0', var_dump($this->Article));
            }
            return TRUE;
        }
        return false;
   }
   
   private function isArticleContext($isK2=false){
        $viewName = 'article';
        if($isK2){
            $viewName = 'item';
        }
        $isArticleView = JRequest::getVar('view') == $viewName ? true : false;
        $hasArticleID = JRequest::getVar('id') != '' ? true : false;
        if($isArticleView && $hasArticleID){
            return true;
        }else{
            return false;
        }
    }
    
    // ---------- Joomla 1.6+ methods ------------------
 
    /**
     * Method is called by the view and the results are imploded and displayed in a placeholder
     * 
     * @var string  $context    The context of the content passed to the plugin
     * @var object  $article    content object. Note $article->text is also available
     * @var object  $params     content params
     * @var integer $limitstart The 'page' number
     * @return String
     * @since 1.6
     */
    public function onContentBeforeDisplay($context, $article, &$params, $limitstart=0){
        if( $context != 'com_content.article' && $context !='com_virtuemart.productdetails'){
            return '';
        }
        if( !$this->isArticleContext() ){
            return '';
        }
        if(!$this->loadClasses($article)){
            return '';
        }
        if($this->params->def('categories') != ''){
            if(!in_array($this->Article->catid, $this->params->def('categories'))){
                return '';
            }
        }
        $FBLikes = new FBLikes($this->Article->url, $this->params->def('width'), $this->params->def('showfaces'));
        $FBLikes->setLayout($this->params->def('layout'));
        return $FBLikes->getHTML();
    }
    

} //End Class