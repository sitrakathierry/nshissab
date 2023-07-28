$(document).ready(function(){
    var instance = new Loading(files.loading)
    var appBase = new AppBase() ;
    $("#search_entrepot_ste").chosen({no_results_text: "Aucun resultat trouvé : "});
    $("#search_categorie_ste").chosen({no_results_text: "Aucun resultat trouvé : "});
    $("#search_produit_ste").chosen({no_results_text: "Aucun resultat trouvé : "});
    $(".chosen_select").chosen({
        no_results_text: "Aucun resultat trouvé : "
    });
    $("#search_produit").chosen({no_results_text: "Aucun resultat trouvé : "});
    $("#search_categorie").chosen({no_results_text: "Aucun resultat trouvé : "});
    
    $("#prod_categorie").chosen({no_results_text: "Aucun resultat trouvé : "});
    $(".crt_entrepot").chosen({no_results_text: "Aucun resultat trouvé : "});
    $(".crt_fournisseur").chosen({no_results_text: "Aucun resultat trouvé : "});

    

    // <button type="button" class="btn-outline-warning edit_appro btn btn-sm font-smaller"><i class="fa fa-edit"></i></button> 
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


    // GENERER CODE BARRE ET QR CODE
    $(document).on('click',"#stock_generer_code_scan", function(){
        if(!$(this).attr("disabled"))
        {
            var realinstance = instance.loading()
            var self = $(this)
            $.ajax({
                url: routes.stock_code_to_scan_generer,
                type:'post',
                cache: false,
                dataType: 'html',
                processData: false,
                contentType: false,
                success: function(response){
                    realinstance.close()
                    $("#contentScanner").empty().html(response) 
                    generateCode($(".code_produit").val())
                },
                error: function(resp){
                    realinstance.close()
                    $.alert(JSON.stringify(resp)) ;
                }
            })
        }
        $(this).prop("disabled", true);
    })

    // NOUVEAU (MANUEL) CODE BARRE ET QR CODE
    $(document).on('click',"#stock_nouveau_code_scan", function(){
        if(!$(this).attr("disabled"))
        {
            var realinstance = instance.loading()
            var self = $(this)
            $.ajax({
                url: routes.stock_code_to_scan_nouveau,
                type:'post',
                cache: false,
                dataType: 'html',
                processData: false,
                contentType: false,
                success: function(response){
                    realinstance.close()
                    $("#contentScanner").empty().html(response)
                    generateCode("000000000000")
                },
                error: function(resp){
                    realinstance.close()
                    $.alert(JSON.stringify(resp)) ;
                }
            })
        }
        $(this).prop("disabled", true);
    })

    function generateCode(dataValue)
    {
        $(".qr_block").html("")
        $(".qr_block").qrcode({
            // render method: 'canvas', 'image' or 'div'
            render: 'image',
            size: 2400,
            text: dataValue,
        });
        $(".qr_code_produit").val($(".qr_block img").attr("src"))
        $(".crt_code").each(function(){
            $(this).val(dataValue) ;
        })
        var barCodeVal = appBase.str_pad(dataValue,12,'0')
        $(".mybarCode").html("")
        if(!appBase.isNumeric(barCodeVal))
        {
            $.alert({
                title: 'Message',
                content: "Désolé, impossible de générer une code barre.<br>Vérifier si tous les caractères sont numériques",
                type: "orange",
            });
            return false ;
        }
        $(".mybarCode").barcode(
            {
                code: barCodeVal,
                rect: false,
            },
            "ean13",
            {
                output: "svg",
                barWidth: 2,
                barHeight: 70,
            }
        );
        $(".barcode_produit").val($(".mybarCode object").attr("data"))
    }

    $(document).on('keyup',".code_produit",function(){
        var self = $(this)
        generateCode(self.val())
    })

    $(".qr_block").qrcode({
        // render method: 'canvas', 'image' or 'div'
        render: 'image',
        size: 2400,
        text: "DEFAULT",
    });

    $(document).on('change',".code_produit",function(){
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
    /*
        Revient = achat + charge
        Vente = revient + marge
    */
    function calculPrix(parent,prixProduit)
    {
        var achat = parent.find(prixProduit.achat).val() != "" ? parent.find(prixProduit.achat).val() : 0
        var charge = parent.find(prixProduit.charge).val() != "" ? parent.find(prixProduit.charge).val() : 0
        var marge = parent.find(prixProduit.marge).val() != "" ? parent.find(prixProduit.marge).val() : 0

        parent.find(prixProduit.revient).val(parseFloat(achat) + parseFloat(charge))
        parent.find(prixProduit.vente).val(parseFloat(achat) + parseFloat(charge) + parseFloat(marge))
    }

    var inputNumber = [
        ".crt_prix_achat",
        ".crt_charge",
        ".crt_prix_revient",
        ".crt_marge",
        ".crt_prix_vente"
    ]

    var prixProduit = 
    {
        achat:".crt_prix_achat",
        charge:".crt_charge",
        revient:".crt_prix_revient",
        marge:".crt_marge",
        vente:".crt_prix_vente",
    }

    function checkInputPrix()
    {
        inputNumber.forEach(elem => {
            $(elem).each(function()
            {
                $(this).keyup(function(){
                    calculPrix($(this).closest(".content_product"),prixProduit) ;
                })
            })
        })
    }
    checkInputPrix()
    countFournisseur()
    $(".add_product_variation").click(function()
    {
        var compteur = $('.content_product').length
        $('.caption_compteur').text("("+compteur+")")
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
                    <input type="number" name="crt_prix_achat[]" id="crt_prix_achat" class="form-control crt_prix_achat" placeholder=". . .">

                    <label for="crt_prix_revient" class="mt-1 font-weight-bold">Prix de revient</label>
                    <input type="number" name="crt_prix_revient[]" readonly id="crt_prix_revient" class="form-control crt_prix_revient" placeholder=". . .">

                    <label for="crt_calcul" class="mt-1 font-weight-bold">Calcul</label>
                    <select name="crt_calcul[]" class="custom-select crt_calcul" id="crt_calcul">
                        `+$('#crt_calcul').html()+`
                    </select>

                    <label for="crt_prix_vente" class="mt-1 font-weight-bold">Prix Vente</label>
                    <input type="number" name="crt_prix_vente[]" readonly id="crt_prix_vente" class="form-control crt_prix_vente" placeholder=". . .">

                    <label for="crt_stock_alert" class="mt-1 font-weight-bold">Stock Alerte</label>
                    <input type="number" name="crt_stock_alert[]" id="crt_stock_alert" class="form-control crt_stock_alert" placeholder=". . .">
                </div>
                <div class="col-md-6 px-4">
                    <div class="mt-2 text-white mb-4 text-right w-100 h3 font-weight-bold">
                        <button type="button" class="btn btn-outline-danger annule_product btn-sm"><i class="fa fa-times"></i></button>
                    </div>
                    <label for="crt_fournisseur" class="mt-2 font-weight-bold">Fournisseur</label>
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
                    <input type="text" name="crt_expiree_le[]" id="crt_expiree_le_`+compteur+`" class="form-control crt_expiree_le" placeholder=". . .">
                </div>
            </div>
        </div>
        `
        $(".all_product").append(content)
        $(".crt_entrepot").chosen({no_results_text: "Aucun resultat trouvé : "});
        $(".crt_fournisseur").chosen({no_results_text: "Aucun resultat trouvé : "});
        countFournisseur()
        closeProduct()
        checkInputPrix()
        $("#crt_expiree_le_"+compteur).datepicker() ;
        $("#crt_expiree_le_"+compteur).val("") ; 
    }) 
    
    $("#crt_expiree_le").datepicker() ;
    $("#crt_expiree_le").val("") ;
    function closeProduct()
    {
        $(".annule_product").click(function(){
            var compteur = $('.content_product').length
            $('.caption_compteur').text("("+compteur+")")
            if(compteur > 1)
                $(this).closest('.content_product').remove() ;
            else
            {
                $.alert({
                    title: "Attention",
                    content: "Il doit y avoir au moins un élément à enregistrer",
                    type: "orange"
                })
            }

        })
    }
    closeProduct()


    var stock_general_search = [
        {
            name: "idC",
            selector : "search_categorie"
        },
        {
            name: "id",
            selector : "search_produit"
        }
    ]

    function searchStockGeneral()
    {
        var instance = new Loading(files.search) ;
        $(".elem_stock_general").html(instance.search(5)) ;
        var formData = new FormData() ;
        for (let j = 0; j < stock_general_search.length; j++) {
            const search = stock_general_search[j];
            formData.append(search.name,$("#"+search.selector).val());
        }
        $.ajax({
            url: routes.stock_search_stock_general ,
            type: 'post',
            cache: false,
            data:formData,
            dataType: 'html',
            processData: false, // important pour éviter la transformation automatique des données en chaîne
            contentType: false, // important pour envoyer des données binaires (comme les fichiers)
            success: function(response){
                $(".elem_stock_general").html(response) ;
            }
        })
    }


    $("#search_categorie").change(function(){
        searchStockGeneral()
    })

    $("#search_produit").change(function(){
        searchStockGeneral()
    })

    var stock_entrepot_search = [
        {
            name: "idE",
            selector : "search_entrepot_ste"
        },
        {
            name: "idC",
            selector : "search_categorie_ste"
        },
        {
            name: "idP",
            selector : "search_produit_ste"
        }
    ]

    function searchStockEntrepot()
    {
        var instance = new Loading(files.search) ;
        $(".elem_stock_entrepot").html(instance.search(7)) ;
        var formData = new FormData() ;
        for (let j = 0; j < stock_entrepot_search.length; j++) {
            const search = stock_entrepot_search[j];
            formData.append(search.name,$("#"+search.selector).val());
        }
        $.ajax({
            url: routes.stock_search_stock_entrepot ,
            type: 'post',
            cache: false,
            data:formData,
            dataType: 'html',
            processData: false, // important pour éviter la transformation automatique des données en chaîne
            contentType: false, // important pour envoyer des données binaires (comme les fichiers)
            success: function(response){
                $(".elem_stock_entrepot").html(response) ;
            }
        })
    }

    var stock_entrepot = [
        "#search_entrepot_ste",
        "#search_categorie_ste",
        "#search_produit_ste"
    ]

    stock_entrepot.forEach(elem => {
        $(elem).change(function()
        {
            searchStockEntrepot()
        })
    })


})

