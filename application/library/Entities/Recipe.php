<?
class Entities_Recipe {
    var $recipe_id;
    var $db;
   function Entities_Recipe($id=0) {
      $this->recipe_id=$id;
      $this->db = Zend_Registry::get('db');
   }
   public function getProperties() {
    if ( $this->recipe_id>0) {
      $select=$this->db->select();
      $select->from("beer_recipes")
      ->join("users","users.user_id=beer_recipes.brewer_id",array("user_name"))
      ->joinLeft("beer_styles","beer_styles.style_id=beer_recipes.recipe_style",array("style_name"))
      ->where("recipe_id = ?",$this->recipe_id);
      $this->properties= $this->db->fetchRow($select);
     return $this->properties;
    }else{
      return false;
    }
   }
}