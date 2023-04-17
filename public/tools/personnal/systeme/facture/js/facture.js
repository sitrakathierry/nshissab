$(document).ready(function(){  
   var facture_editor = new LineEditor(".facture_editor") ;
   // facture_editor.setEditorText("Bonjour tout le monde <i class='fa fa-hand'></i>")
   $(".enregistre_create_facture").click(function(){
    console.log(facture_editor.getEditorText('.facture_editor'))
   })
})