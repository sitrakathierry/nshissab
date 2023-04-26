$(document).ready(function(){
    var produit_editor = new LineEditor(".produit_editor") ;
    var instance = new Loading(files.loading)
    var appBase = new AppBase() ;
    $(".appr_ajout").click(function(){
        var self = $(this)
        $.confirm({
            boxWidth: '800px',
            useBootstrap: false,
            title:"Approvisionnement Type : <span class='text-warning'>"+self.attr('caption')+"</span>",
            content: `
            <div class="w-100 container-fluid">
                <div class="row">
                    <div class="col-md-4 text-left">
                        <label for="nom" class="font-weight-bold">Entrepôt</label>
                        <select name="type_societe" class="custom-select custom-select-sm" id="type_societe">
                            <option value="">Tous</option>
                        </select>
                    </div>
                    <div class="col-md-4 text-left">
                        <label for="nom" class="font-weight-bold">Produit</label>
                        <select name="type_societe" class="custom-select custom-select-sm" id="type_societe">
                            <option value="">Tous</option>
                        </select>
                    </div>
                    <div class="col-md-4 text-left">
                        <label for="nom" class="font-weight-bold">Prix Produit</label>
                        <select name="type_societe" class="custom-select custom-select-sm" id="type_societe">
                            <option value="">Tous</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 text-left">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="nom" class="mt-1 font-weight-bold">Indice</label>
                                <input type="text" name="nom" id="nom" class="form-control" placeholder=". . .">
                            </div>
                            <div class="col-md-6">
                                <label for="nom" class="mt-1 font-weight-bold">Quantité</label>
                                <input type="number" name="nom" id="nom" class="form-control" placeholder=". . .">
                            </div>
                        </div>

                        <label for="nom" class="mt-1 font-weight-bold">Fournisseurs</label>
                        <select name="type_societe" class="custom-select custom-select-sm" id="type_societe">
                            <option value="">Tous</option>
                        </select>

                        <label for="nom" class="mt-1 font-weight-bold">Expirée le</label>
                        <input type="text" name="nom" id="nom" class="form-control" placeholder=". . .">
                    </div>
                    <div class="col-md-6 text-left">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="nom" class="mt-1 font-weight-bold">Prix Achat</label>
                                <input type="number" name="nom" id="nom" class="form-control" placeholder=". . .">
                            </div>
                            <div class="col-md-6">
                                <label for="nom" class="mt-1 font-weight-bold">Charge</label>
                                <input type="number" name="nom" id="nom" class="form-control" placeholder=". . .">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label for="nom" class="mt-1 font-weight-bold">Marge type</label>
                                <select name="type_societe" class="custom-select custom-select-sm" id="type_societe">
                                    <option value="">Tous</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="nom" class="mt-1 font-weight-bold">Marge valeur</label>
                                <input type="text" name="nom" id="nom" class="form-control" placeholder=". . .">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label for="nom" class="mt-1 font-weight-bold">Prix de revient</label>
                                <input type="text" name="nom" id="nom" class="form-control" placeholder=". . .">
                            </div>
                            <div class="col-md-6">
                                <label for="nom" class="mt-1 font-weight-bold">Prix de vente</label>
                                <input type="text" name="nom" id="nom" class="form-control" placeholder=". . .">
                            </div>
                        </div>
                    </div>
                </div>
                <h6 class="font-weight-bold mt-1">Montant Total : <span class="text-warning">10000 KMF</span></h6>
            </div>
            `,
            theme:"modern",
            type:'blue',
            buttons:{
                btn1:{
                    text: 'Annuler',
                    action: function(){}
                },
                btn2:{
                    text: 'Ajouter',
                    btnClass: 'btn-blue',
                    keys: ['enter', 'shift'],
                    action: function(){
                        $.alert("Ajoutée !!! ")
                    }
                }
            }
        })
    })

    $(".importImage").click(function(){
        $("#imageImport").click()
    })


    $('#imageImport').on('change', function() {
        var reader = new FileReader();
        reader.onloadend = function() {
          // Afficher les données du fichier
          var image = new CustomImage(reader.result)
          var basePromise = image.limitBase64ImageSize(2097152)
          basePromise.then(base64 => {
            $(".image_categorie").attr("src",base64)
          });
        }
        // Lire le contenu du fichier
        reader.readAsDataURL(this.files[0]);
      });
    $(".save_prd_categorie").click(function(){
        var elements = [
            {
                selector:"#nom",
                title:"Nom",
                type:"orange"
            }  
        ]
        var appBase = new AppBase()
        var allow = appBase.checkData(elements)
        if(!allow)
            return
        $.confirm({
            title: "Confirmation",
            content:"Vous êtes sûre de vouloir enregistrer ?",
            type:"blue",
            theme:"modern",
            buttons:{
                btn1:{
                    text: 'Non',
                    action: function(){}
                },
                btn2:{
                    text: 'Oui',
                    btnClass: 'btn-blue',
                    keys: ['enter', 'shift'],
                    action: function(){
                        var realinstance = instance.loading()
                        var nom = $("#nom").val() ;
                        var id = $(".id_categorie").val()
                        var image = $(".image_categorie").attr("src")
                        $.ajax({
                            url: routes.stock_save_categorie,
                            cache: false,
                            type: 'post',
                            data:{image:image,nom:nom,id:id},
                            dataType:'json',
                            success: function(res){
                                realinstance.close()
                                $.alert({
                                    title: 'Message',
                                    content: res.message,
                                    type: res.type,
                                    buttons : {
                                        OK: function(){
                                            if(res.type == "dark")
                                            {
                                                location.assign(routes.stock_cat_consultation)
                                            }
                                            else
                                            {
                                                $.confirm({
                                                    title: "Consultation",
                                                    content:"Voulez-vous consulter la liste des catégories ?",
                                                    type:"purple",
                                                    theme:"modern",
                                                    buttons:{
                                                        btn1:{
                                                            text: 'Non',
                                                            action: function(){
                                                                $("#nom").val("")
                                                                $("#imageImport").val("")
                                                                location.reload()
                                                            }
                                                        },
                                                        btn2:{
                                                            text: 'Oui',
                                                            btnClass: 'btn-purple',
                                                            keys: ['enter', 'shift'],
                                                            action: function(){
                                                                location.assign(routes.stock_cat_consultation)
                                                            }
                                                        }
                                                    }
                                                })
                                            }
                                                
                                        } 
                                    }
                                });
        
                            }
                        })
                    }
                }
            }
        })
    })

    $(".delete_prd_categorie").click(function(){
    var self = $(this)
    $.confirm({
        title: "Confirmation",
        content:"Vous êtes sûre de vouloir supprimer ?",
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
                keys: ['enter', 'shift'],
                action: function(){
                    var realinstance = instance.loading()
                    $.ajax({
                        url: routes.stock_delete_categorie,
                        cache: false,
                        type: 'post',
                        data:{id:self.closest('tr').attr("value")},
                        dataType:'json',
                        success: function(res){
                            realinstance.close()
                            $.alert({
                                title: 'Message',
                                content: res.message,
                                type: res.type,
                                buttons : {
                                    OK: function(){
                                        location.reload()
                                    }
                                }
                            });
    
                        }
                    })
                }
            }
        }
    })
    })

    $(".btn_recherche").click(function(){
        location.assign(routes.stock_cat_consultation+"/"+$("#rch_nom").val())
    })

    $("#formEntrepot").submit(function(event){
        event.preventDefault()
        var data = $(this).serialize();
        $.confirm({
            title: 'Confirmation',
            content:"Voulez-vous vraiment enregistrer ?",
            type:"blue",
            theme:"modern",
            buttons : {
                NON : function(){
                    $("#nom").val("")
                    $("#adresse").val("")
                    $("#telephone").val("")
                },
                OUI : 
                {
                    text: 'OUI',
                    btnClass: 'btn-blue',
                    keys: ['enter', 'shift'],
                    action: function(){
                        var realinstance = instance.loading()
                        $.ajax({
                            url: routes.stock_save_entrepot,
                            type:"post",
                            data:data,
                            dataType:"json",
                            success : function(json){
                                realinstance.close()
                                $.alert({
                                    title: 'Message',
                                    content: json.message,
                                    type: json.type,
                                });
                                if(json.type == "green")
                                {
                                    var newItem = `
                                    <tr>
                                        <td>`+$("#nom").val()+`</td>
                                        <td>`+$("#adresse").val()+`</td>
                                        <td>`+$("#telephone").val()+`</td>
                                    <td class="text-center"><button class="btn btn-sm btn-outline-warning font-smaller"><i class="fa fa-edit"></i></button>&emsp;<button class="btn btn-sm btn-outline-danger font-smaller" ><i class="fa fa-trash"></i></button></td>
                                    `
                                    $(".elem_entrepots").append(newItem) ;
                                    
                                    $("#nom").val("")
                                    $("#adresse").val("")
                                    $("#telephone").val("")
                                }
                            }
                        })
                    }
                }
            }
        })
    })

    function editEntrepot()
    {
        $(".edit_entrepot").click(function(){
            var self = $(this)
            var realIns1 = instance.loading()
            $.ajax({
                url: routes.stock_edit_entrepot,
                type:'post',
                cache:false,
                data:{id:self.closest('tr').attr('value')},
                dataType: 'json',
                success: function(resp){
                    realIns1.close()
                    $.confirm({
                        title: "Modification Entrepot",
                        content:`
                        <div class="w-100 text-left">
                            <label for="nom" class=" font-weight-bold">Nom</label>
                            <input type="text" name="nom" id="edit_nom" oninput="this.value = this.value.toUpperCase();" class="form-control" value="`+resp.nom+`" placeholder=". . .">
    
                            <label for="adresse" class="text-left font-weight-bold">Adresse</label>
                            <input type="text" name="adresse" id="edit_adresse" class="form-control" value="`+resp.adresse+`" placeholder=". . .">
       
                            <label for="telephone" class="text-left font-weight-bold">Tél</label>
                            <input type="text" name="telephone" id="edit_telephone" class="form-control" value="`+resp.telephone+`" placeholder=". . .">
                    </div>
                        `,
                        type:"orange",
                        theme:"modern",
                        buttons:{
                            btn1:{
                                text: 'Annuler',
                                action: function(){}
                            },
                            btn2:{
                                text: 'Mettre à jour',
                                btnClass: 'btn-orange',
                                keys: ['enter'],
                                action: function(){
                                    var realIns2 = instance.loading()
                                    $.ajax({
                                        url: routes.stock_save_entrepot,
                                        type:'post',
                                        cache:false,
                                        data:{
                                            id:resp.id,
                                            nom:$("#edit_nom").val(),
                                            adresse:$("#edit_adresse").val(),
                                            telephone:$("#edit_telephone").val(),
                                        },
                                        dataType:'json',
                                        success: function(json){
                                            realIns2.close()
                                            $.alert({
                                                title: 'Message',
                                                content: json.message,
                                                type: json.type,
                                                buttons: {
                                                    OK : function(){
                                                        if(json.type == "green")
                                                        {
                                                            location.reload()
                                                        }
                                                    }
                                                }
                                            });
                                        }
                                    })
                                }
                            }
                        }
                    })
                }
            })
        })
    }
    editEntrepot()
    function deleteEntrepot()
    {
        $(".delete_entrepot").click(function(){
            var self = $(this)
            $.confirm({
                title: "Suppression",
                content:"Vous êtes sûre de vouloir supprimer cet éléments ?",
                type:"red",
                theme:"modern",
                buttons:{
                    btn1:{
                        text: 'Annuler',
                        action: function(){}
                    },
                    btn2:{
                        text: 'Supprimer',
                        btnClass: 'btn-red',
                        keys: ['enter'],
                        action: function(){
                            var realIns2 = instance.loading()
                            $.ajax({
                                url: routes.stock_delete_entrepot,
                                type:'post',
                                cache:false,
                                data:{id:self.closest('tr').attr('value')},
                                dataType:'json',
                                success: function(json){
                                    realIns2.close()
                                    $.alert({
                                        title: 'Message',
                                        content: json.message,
                                        type: json.type,
                                        buttons: {
                                            OK : function(){
                                                if(json.type == "green")
                                                {
                                                    location.reload()
                                                }
                                            }
                                        }
                                    });
                                }
                            })
                        }
                    }
                }
            })
        })
    }
    deleteEntrepot()

    var entrepot_search = [
        {
            name: "nom",
            selector : "rch_nom"
        },
        {
            name: "adresse",
            selector : "rch_adresse"
        },
        {
            name: "telephone",
            selector : "rch_tel"
        }
    ]

    for (let i = 0; i < entrepot_search.length; i++) {
        const element = entrepot_search[i];
        $("#"+element.selector).keyup(function(){
                appBase.searchElement(
                ".elem_entrepots",
                routes.stock_search_entrepot,
                entrepot_search,
                4)
        })
    }

    $('.vider').click(function(){
        appBase.searchElement(
            ".elem_entrepots",
            routes.stock_search_entrepot,
            entrepot_search,
            4)
    })

    $("#list_preferences").chosen({no_results_text: "Aucun resultat trouvé : "});

    $(".ajout_pref").click(function(){
        $.confirm({
            title: "Confirmation",
            content:"Vous êtes sûre de vouloir enregistrer ?",
            type:"blue",
            theme:"modern",
            buttons:{
                btn1:{
                    text: 'Non',
                    action: function(){
                        $("#list_preferences").val("")
                        $("#list_preferences").trigger("chosen:updated");
                    }
                },
                btn2:{
                    text: 'Oui',
                    btnClass: 'btn-blue',
                    keys: ['enter', 'shift'],
                    action: function(){
                        var preferences = $("#list_preferences").val()
                        $(".content_prefs").html(instance.search(2))
                        var dataPrefs = new FormData() ;
                        dataPrefs.append("preferences[]",preferences) ;
                        $.ajax({
                            url: routes.stock_save_prefs,
                            type : 'post',
                            cache: false,
                            data:dataPrefs,
                            dataType:'html',
                            processData: false, // important pour éviter la transformation automatique des données en chaîne
                            contentType: false, // important pour envoyer des données binaires (comme les fichiers)
                            success: function(resp){
                                $(".content_prefs").html(resp)
                                $("#list_preferences").val("")
                                $("#list_preferences").trigger("chosen:updated");
                                location.reload()
                            }
                        })
                    }
                }
            }
        })
    })

    $(".delete_prefs").click(function(){
        var self = $(this)
        $.confirm({
            title: "Confirmation",
            content:"Vous êtes sûre de vouloir supprimer ?",
            type:"red",
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
                    btnClass: 'btn-red',
                    keys: ['enter', 'shift'],
                    action: function(){
                        var realinstance = instance.loading() ;
                        $.ajax({
                            url: routes.stock_delete_prefs,
                            type: 'post',
                            cache: false,
                            data:{id:self.closest('tr').attr("value")},
                            dataType:'json',
                            success: function(resp){
                                realinstance.close()
                                $.alert({
                                    title: 'Message',
                                    content: resp.message,
                                    type: resp.type,
                                    buttons : {
                                        OK: function(){
                                            location.reload()
                                        }
                                    }
                                });
                            }   
                        })
                    }
                }
            }
        })
    })
})