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

var realinstance = instance.loading()
$.ajax({
    url: ,
    type:'post',
    cache: false,
    data: ,
    dataType: 'html',
    processData: false,
    contentType: false,
    success: function(response){
        realinstance.close()
        $.alert({
            title: 'Message',
            content: json.message,
            type: json.type,
            buttons: {
                OK: function(){
                    if(json.type == "green")
                    {
                        location.reload()
                    }
                }
            }
        });
    },
    error: function(resp){
        realinstance.close()
        $.alert(JSON.stringify(resp)) ;
    }
})

oninput="this.value = this.value.toUpperCase();"

Au moment de mettre les données réels, effacer la table : 

CmdBonCommande, CaisseCommande, CaissePanier, ...

var self = $(this)
        $.confirm({
            title: "Suppression",
            content:"Êtes-vous sûre ?",
            type:"red",
            theme:"modern",
            buttons:{
                btn1:{
                    text: 'Non',
                    action: function(){}
                },
                btn2:{
                    text: 'Oui',
                    btnClass: 'btn-red',
                    keys: ['enter'],
                    action: function(){
                        var realinstance = instance.loading()
                        $.ajax({
                            url: routes.stock_delete_fournisseur,
                            type:'post',
                            cache: false,
                            data:{test:self.data("value")},
                            dataType: 'json',
                            success: function(json){
                                realinstance.close()
                                $.alert({
                                    title: 'Message',
                                    content: json.message,
                                    type: json.type,
                                    buttons: {
                                        OK: function(){
                                            if(json.type == "green")
                                            {
                                                location.reload()
                                            }
                                        }
                                    }
                                });
                            },
                            error: function(resp){
                                realinstance.close()
                                $.alert(JSON.stringify(resp)) ;
                            }
                        })
                    }
                }
            }
        })
        return false ;

        
        $filename = $this->filename."releveloyer(agence)/".$this->nameAgence."_relevePL_".$id  ;
        if(!file_exists($filename))
            $this->appService->generateLctRelevePaiementLoyer($filename,$id) ;

        $relevePaiements = json_decode(file_get_contents($filename)) ;

    // DEBUT SAUVEGARDE HISTORIQUE

    $this->entityManager->getRepository(HistoHistorique::class)
    ->insererHistorique([
        "refModule" => "CSE",
        "nomModule" => "CAISSE",
        "refAction" => "CRT",
        "user" => $this->userObj,
        "agence" => $this->agence,
        "nameAgence" => $this->nameAgence,
        "description" => "Vente sur Caisse ; Bon de Caisse N° : ".$numCommande,
    ]) ;

    // FIN SAUVEGARDE HISTORIQUE


        #[Route('/prestation/location/paiement/search', name: 'prest_location_paiement_search')]
    public function prestSearchPaiementLocation(Request $request)
    {
        
    }