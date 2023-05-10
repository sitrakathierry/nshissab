Nouvelle version de Shissab 
Il faut savoir que les conditions pour l'affichage des menus utilisateurs ce sont ceux qui sont inclus dans de tableau des menus special admin


$.confirm({
    title: "Confirmation",
    content:"Vous êtes sûre de vouloir enregistrer ?",
    type:"blue",
    theme:"modern",
    buttons:{
        btn1:{
            text: 'Non',
            action: function(){
                $("#nom").val("")
                $("#imageImport").val("")
            }
        },
        btn2:{
            text: 'Oui',
            btnClass: 'btn-blue',
            keys: ['enter', 'shift'],
            action: function(){
                
            }
        }
    }
})


$.ajax({
    url: ,
    type:'post',
    cache: false,
    data: ,
    dataType: 'json',
    success: function(){

    },
    error: function(resp){
        $.alert(JSON.stringify(resp)) ;
    }
})