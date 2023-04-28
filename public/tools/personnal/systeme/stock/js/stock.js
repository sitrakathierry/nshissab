$(document).ready(function(){
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

    function searchEntrepot()
    {
        var instance = new Loading(files.search) ;
            $(".elem_entrepots").html(instance.search(4)) ;
            var formData = new FormData() ;
            for (let j = 0; j < entrepot_search.length; j++) {
                const search = entrepot_search[j];
                formData.append(search.name,$("#"+search.selector).val());
            }
            $.ajax({
                url: routes.stock_search_entrepot ,
                type: 'post',
                cache: false,
                data:formData,
                dataType: 'html',
                processData: false, // important pour éviter la transformation automatique des données en chaîne
                contentType: false, // important pour envoyer des données binaires (comme les fichiers)
                success: function(response){
                    $(".elem_entrepots").html(response) ;
                    editEntrepot()
                    deleteEntrepot()
                }
            })
    }

    for (let i = 0; i < entrepot_search.length; i++) {
        const element = entrepot_search[i];
        $("#"+element.selector).keyup(function(){
            searchEntrepot() ;
        })
    }

    $('.vider').click(function(){
        searchEntrepot() ;
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

    $("#prod_categorie").chosen({no_results_text: "Aucun resultat trouvé : "});
    $(".crt_entrepot").chosen({no_results_text: "Aucun resultat trouvé : "});
    $(".crt_fournisseur").chosen({no_results_text: "Aucun resultat trouvé : "});

    $(".code_produit").keyup(function(){
        var self = $(this)
        $(".qr_block").html("")
        $(".qr_block").qrcode({
            // render method: 'canvas', 'image' or 'div'
            render: 'image',
            size: 2400,
            text: self.val(),
        });
        $(".qr_code_produit").val($(".qr_block img").attr("src"))
        $(".crt_code").each(function(){
            $(this).val(self.val()) ;
        })
    })
    
    $(".qr_block").qrcode({
        // render method: 'canvas', 'image' or 'div'
        render: 'image',
        size: 2400,
        text: "DEFAULT",
    });

    $(".code_produit").change(function(){
        var self = $(this)
        $.ajax({
            url: routes.stock_check_codeProduit,
            type: 'post',
            cache: false,
            data:{codeProduit:self.val()},
            dataType: 'json',
            success: function(resp){
                if(resp.type == "orange")
                {
                    $.alert({
                        title: resp.title,
                        content: resp.message,
                        type: resp.type,
                        buttons: {
                            OK: function(){
                                self.val("")
                            }
                        }
                    })
                }
            }
        })
    })

    var produit_editor = new LineEditor(".produit_editor") ;

    $("#formFournisseur").submit(function(event){
        event.preventDefault()  
        var data = $(this).serialize();
        $.confirm({
            title: 'Confirmation',
            content:"Voulez-vous vraiment enregistrer ?",
            type:"blue",
            theme:"modern",
            buttons : {
                NON : function(){
                    
                },
                OUI : function(){
                    $(".content_fournisseur").html(instance.search(7))
                    $.ajax({
                        url: routes.stock_save_fournisseur,
                        type:"post",
                        data:data,
                        dataType:"html",
                        success : function(resp){
                            $(".content_fournisseur").html(resp) ;
                            var elements = data.split("&") ;
                            elements.forEach(elem => {
                                $("#"+elem.split("=")[0]).val("")
                            })

                        }
                    })
                }
            }
        })
    })

    $("#formCreateProduit").submit(function(event){
        event.preventDefault()
        var self = $(this)
        $(".produit_editor").text(produit_editor.getEditorText()) 
        $.confirm({
            title: 'Confirmation',
            content:"Voulez-vous vraiment enregistrer ?",
            type:"blue",
            theme:"modern",
            buttons : {
                NON : function()
                {
                    $('input, select').val('');
                    $("#prod_categorie").trigger("chosen:updated");
                    $(".crt_entrepot").trigger("chosen:updated");
                    $(".crt_fournisseur").trigger("chosen:updated");
                    location.reload()
                },
                OUI : function(){
                    var crt_frns_vide = false
                    $(".crt_fournisseur").each(function(){
                        if($(this).val().length == 0)
                        {
                            crt_frns_vide = true ;
                            return ;
                        }
                    })

                    if(crt_frns_vide)
                    {
                        $.alert({
                            title: 'Fournisseur vide',
                            content: "Veuillez séléctionner au moins un fournisseur",
                            type:'orange',
                        })
                        return ;
                    }
                    var data = self.serialize();
                    var realinstance = instance.loading()
                    $.ajax({
                        url: routes.stock_save_creationProduit,
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
                                $('input, select').val('');
                                $("#prod_categorie").trigger("chosen:updated");
                                $(".crt_entrepot").trigger("chosen:updated");
                                $(".crt_fournisseur").trigger("chosen:updated");
                                location.reload()
                            }
                        }
                    })
                }
            }
        })
        
    })

    function countFournisseur()
    {
        $(".crt_fournisseur").change(function(){
            var countFrns = $(this).closest('div').find(".crt_count_fournisseur")
            countFrns.val($(this).val().length)
        })
    }
    countFournisseur()
    compteur = 1
    $(".add_product_variation").click(function(){
        var content = `
        <div class="content_product mt-5 container-fluid rounded w-100 py-3 shadow">
            <div class="row"> 
                <div class="col-md-6 px-4">
                    <div class="row">
                        <div class="col-md-8">
                            <label for="crt_code" class="mt-2 font-weight-bold">Code</label>
                            <input type="text" name="crt_code[]" id="crt_code" value="`+$('.code_produit').val()+`" class="form-control crt_code" readonly placeholder=". . .">
                        </div>
                        <div class="col-md-4">
                            <label for="crt_indice" class="mt-2 font-weight-bold">Indice</label>
                            <input type="text" name="crt_indice[]" oninput="this.value = this.value.toUpperCase();" id="crt_indice" class="form-control crt_indice" placeholder=". . .">
                        </div>
                    </div>
                    
                    <label for="crt_entrepot" class="mt-1 font-weight-bold">Entrepot</label>
                    <select name="crt_entrepot[]" class="custom-select crt_entrepot" id="crt_entrepot">
                        `+$('#crt_entrepot').html()+`
                    </select>

                    <label for="crt_prix_achat" class="mt-2 font-weight-bold">Prix Achat</label>
                    <input type="number" name="crt_prix_achat[]" id="ncrt_prix_achatom" class="form-control ncrt_prix_achatom" placeholder=". . .">

                    <label for="crt_prix_revient" class="mt-1 font-weight-bold">Prix de revient</label>
                    <input type="number" name="crt_prix_revient[]" id="crt_prix_revient" class="form-control crt_prix_revient" placeholder=". . .">

                    <label for="crt_calcul" class="mt-1 font-weight-bold">Calcul</label>
                    <select name="crt_calcul[]" class="custom-select crt_prix_revient" id="crt_calcul">
                        `+$('#crt_calcul').html()+`
                    </select>

                    <label for="crt_prix_vente" class="mt-1 font-weight-bold">Prix Vente</label>
                    <input type="number" name="crt_prix_vente[]" id="crt_prix_vente" class="form-control crt_prix_vente" placeholder=". . .">

                    <label for="crt_stock_alert" class="mt-1 font-weight-bold">Stock Alerte</label>
                    <input type="number" name="crt_stock_alert[]" id="crt_stock_alert" class="form-control crt_stock_alert" placeholder=". . .">
                </div>
                <div class="col-md-6 px-4">
                    <label for="nom" class="mt-2 text-white mb-0 text-right annule_product w-100 h3 font-weight-bold">&times;</label>
                    <label for="nom" class="w-100 font-weight-bold">&nbsp;</label>

                    <label for="crt_fournisseur" class="mt-0 font-weight-bold">Fournisseur</label>
                    <select name="crt_fournisseur[][]" class="custom-select crt_fournisseur" multiple id="crt_fournisseur">
                    `+$('#crt_fournisseur').html()+`
                    </select>

                    <input type="hidden" name="crt_count_fournisseur[]" value="0" class="crt_count_fournisseur" >

                    <label for="crt_charge" class="mt-2 font-weight-bold">Charge</label>
                    <input type="number" name="crt_charge[]" id="crt_charge" class="form-control crt_charge" placeholder=". . .">

                    <label for="nom" class="w-100 font-weight-bold">&nbsp;</label>
                    <label for="nom" class="w-100 font-weight-bold">&nbsp;</label>

                    <label for="crt_marge" class="mt-3 font-weight-bold">Marge</label>
                    <input type="number" name="crt_marge[]" id="crt_marge" class="form-control crt_marge" placeholder=". . .">

                    <label for="crt_stock" class="mt-1 font-weight-bold">Stock</label>
                    <input type="number" name="crt_stock[]" id="crt_stock" class="form-control crt_stock" placeholder=". . .">

                    <label for="crt_expiree_le" class="mt-1 font-weight-bold">Expirée le</label>
                    <input type="date" name="crt_expiree_le[]" id="crt_expiree_le" class="form-control crt_expiree_le" placeholder=". . .">
                </div>
            </div>
        </div>
        `
        $(".all_product").append(content)

        $(".crt_entrepot").chosen({no_results_text: "Aucun resultat trouvé : "});
        $(".crt_fournisseur").chosen({no_results_text: "Aucun resultat trouvé : "});
        countFournisseur()
        closeProduct()

        compteur = compteur + 1 ; 
        $(".crt_title_form").text("Variation produit : Prix & indice ("+compteur+")") ;
    })
    
    function closeProduct()
    {
        $(".annule_product").click(function(){
            $(this).closest('.content_product').remove() ;
            compteur = compteur - 1 ; 
            $(".crt_title_form").text("Variation produit : Prix & indice ("+compteur+")") ;
        })
    }
    closeProduct()

    var prixProduit = 
    {
        achat:"",
        charge:"",
        revient:"",
        marge:"",
        vente:"",
    }

    function calculPrix(parent,prixProduit)
    {

    }
})