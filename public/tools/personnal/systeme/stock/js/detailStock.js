$(document).ready(function(){
    var produit_details_editor = new LineEditor(".produit_details_editor") ;
    var instance = new Loading(files.loading)
    var appBase = new AppBase() ;
    $("#prod_variation_expiree").datepicker()

    produit_details_editor.setEditorText($(".produit_details_editor").text())
    $(".mybarCode").html("")

    $(".mybarCode").barcode(
        {
            code: $(".details_barcode_produit").val(),
            rect: false,
        },
        "code128",
        {
            output: "svg",
            fontSize: 25,
            bgColor: "transparent",
            barWidth: 3,
            barHeight: 100,
        }
    );
    
    $(".qr_block").html("")

    $(".qr_block").qrcode({
        // render method: 'canvas', 'image' or 'div'
        render: 'image' ,
        size: 2400,
        text: $(".details_qr_code_produit").val(),
    });
    
    
    $("#formDetailProduit").submit(function(){

        var self = $(this)
        $(".produit_details_editor").text(produit_details_editor.getEditorText()) 
        $.confirm({
            title: 'Mise à jour',
            content:"Êtes vous sûre ?",
            type:"orange",
            theme:"modern",
            buttons : {
                NON : function(){},
                OUI : function(){
                    var data = self.serialize();
                    var realinstance = instance.loading()
                    $.ajax({
                        url: routes.stock_update_produit,
                        type:"post",
                        data:data,
                        dataType:"json",
                        success : function(json){
                            realinstance.close()
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
                        },
                        error: function(resp){
                            realinstance.close()
                            $.alert(JSON.stringify(resp)) ;
                        }
                    })
                }
            }
        })
        return false ;
    })

    function getPrdDesignation(url)
    {
        var realinstance = instance.loading()
        var self = $(this)
        $.ajax({
            url: url,
            type:'post',
            cache: false,
            dataType: 'html',
            processData: false,
            contentType: false,
            success: function(response){
                realinstance.close()
                $(".contentPrdDesignation").empty().html(response) 
            },
            error: function(resp){
                realinstance.close()
                $.alert(JSON.stringify(resp)) ;
            }
        })
    }

    $(document).on('click',".prd_new_designation",function(){
        if(!$(this).attr("disabled"))
        {
            getPrdDesignation(routes.stock_get_new_designation)
        }
        $(this).prop("disabled", true);
    })

    $(document).on('click',".prd_existing_designation",function(){
        if(!$(this).attr("disabled"))
        {
            var realinstance = instance.loading()
            var self = $(this)
            var formData = new FormData() ;
            formData.append("type",$("#prod_type").val()) ;
            $.ajax({
                url: routes.stock_get_existing_designation,
                type:'post',
                cache: false,
                data: formData,
                dataType: 'html',
                processData: false,
                contentType: false,
                success: function(response){
                    realinstance.close()
                    $(".contentPrdDesignation").empty().html(response) 
                },
                error: function(resp){
                    realinstance.close()
                    $.alert(JSON.stringify(resp)) ;
                }
            })
        }
        $(this).prop("disabled", true);
    })

    $(document).on("change","#prod_type",function(){
        if(!$(this).is("select") || $(this).hasClass("not-reload"))
            return false ;

        var realinstance = instance.loading()
        var self = $(this)
        var formData = new FormData() ;
        formData.append("type",self.val()) ;
        $.ajax({
            url: routes.stock_get_existing_designation,
            type:'post',
            cache: false,
            data: formData,
            dataType: 'html',
            processData: false,
            contentType: false,
            success: function(response){
                realinstance.close()
                $(".contentPrdDesignation").empty().html(response) 
            },
            error: function(resp){
                realinstance.close()
                $.alert(JSON.stringify(resp)) ;
            }
        })
    })

    $("#formVariationProduit").submit(function(){
        var self = $(this)
        $.confirm({
            title: "Confirmation",
            content:"Vous êtes sûre ?",
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
                        var data = self.serialize()
                        $.ajax({
                            url: routes.stock_variation_produit_save,
                            type:'post',
                            cache: false,
                            data:data,
                            dataType: 'json',
                            success: function(json){
                                realinstance.close() ;
                                $.alert({
                                    title: 'Message',
                                    content: json.message,
                                    type: json.type,
                                    buttons: {
                                        OK: function(){
                                            if(json.type == "green")
                                            {
                                                $("#prod_variation_entrepot").val("")
                                                $("#prod_variation_fournisseur").val("")
                                                $("#prod_variation_prix_vente").val("")
                                                $("#prod_variation_stock").val("")
                                                $("#prod_variation_expiree").val("")

                                                $(".chosen_select").trigger("chosen:updated") ;
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
    }) 

    $(document).on("click",".prod_edit_variation",function(){
        var prd_list_id = $(this).closest("tr").find(".prd_list_id").val()
        var realinstance = instance.loading()
        var formData = new FormData() ;
        formData.append("prd_list_id",prd_list_id)
        $.ajax({
            url: routes.stock_get_details_variation_prix,
            type:"post",
            data:formData,
            dataType:"html", 
            processData: false,
            contentType: false,
            success : function(response){
                realinstance.close()
                $(".detailVariationProduit").html(response)
            },
            error: function(resp){
                realinstance.close()
                $.alert(JSON.stringify(resp)) ;
            }
        })
    })

    $(document).on('submit','#formModifVariation',function(){
        var self = $(this)
        $.confirm({
            title:'Modification',
            content:'Êtes vous sûre ?',
            type:"orange",
            theme:"modern",
            buttons : {
                NON : function(){},
                OUI : function(){
                    var realinstance = instance.loading()
                    var data = self.serialize()
                    $.ajax({
                        url: routes.stock_update_variation_prix,
                        type:"post",
                        data:data,
                        dataType:"json",
                        success : function(json){
                            realinstance.close()
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
                        },
                        error: function(resp){
                            realinstance.close()
                            $.alert(JSON.stringify(resp)) ;
                        }
                    })
                }
            }
        })
        return false ;
    })

    $(document).on('click','.prod_annule_variation',function(){
        $(".detailVariationProduit").html("")
    })

    $(document).on('click',".modif_btn_solder",function(){
        $("#modif_inpt_prix").attr("readonly",true) ;
        var contenu1 = `
            <div class="row">
                <div class="col-md-3">
                    <label for="modif_inpt_solde_type" class="mt-2 font-weight-bold text-primary">Type Solde</label>
                    <select name="modif_inpt_solde_type" class="custom-select custom-select-sm" id="modif_inpt_solde_type">
                        <option value="1">Montant</option>
                        <option value="2">%</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="modif_inpt_solde" class="mt-2 font-weight-bold text-primary">Solde</label>
                    <input type="number" step="any" name="modif_inpt_solde" id="modif_inpt_solde" class="form-control" value="`+$("#modif_inpt_prix").val()+`" placeholder=". . ."> 
                </div>
                <div class="col-md-3">
                    <label for="modif_inpt_solde_date" class="mt-2 font-weight-bold text-primary">Date Limite</label>
                    <input type="text" name="modif_inpt_solde_date" id="modif_inpt_solde_date" class="form-control" placeholder=". . ."> 
                </div>
            </div>
            <script>
                $("#modif_inpt_solde_date").datepicker()
            </script>
            `;
        
        var contenu2 = `
            <label for="" class="font-weight-bold">&nbsp;</label>
            <button class="btn btn-sm btn-dark modif_btn_annuler btn-block"><i class="fa fa-times"></i>&nbsp;Annuler</button>
        `;
        
        $("#contentSolder").empty().html(contenu1) 
        $("#contentBtnSolder").empty().html(contenu2) 
    })
    
    $(document).on('click',".modif_btn_annuler",function(){
        $("#modif_inpt_prix").removeAttr("readonly") ;
        $("#contentSolder").html("") 
        $("#contentBtnSolder").html(`
            <label for="nom" class="font-weight-bold">&nbsp;</label>
            <button class="btn btn-sm btn-primary modif_btn_solder btn-block"><i class="fa fa-percent"></i>&nbsp;Solder</button>
        `) 
    })

    $(document).on('click',".modif_btn_deduire",function(){
        var self = $(this)
        $.confirm({
            title: "Déduction",
            content:`
                <div class="w-100 text-left">
                    <div class="row">
                        <div class="col-6">
                            <label for="quantite_stock" class="font-weight-bold">Stock Actuel</label>
                            <input type="number" readonly step="any" name="quantite_stock" id="quantite_stock" value="`+self.data("stock")+`" class="form-control" placeholder=". . .">
                        </div>
                        <div class="col-6">
                            <label for="reduc_qte" class="font-weight-bold">Qte à reduire</label>
                            <input type="number" step="any" name="reduc_qte" id="reduc_qte" class="form-control" placeholder=". . .">
                        </div>
                    </div>

                    <label for="quantite_restant" class="mt-2 font-weight-bold">Stock restant</label>
                    <input type="number" readonly step="any" name="quantite_restant" id="quantite_restant" class="form-control" placeholder=". . .">

                    <label for="reduc_type" class="mt-2 font-weight-bold">Type</label>
                    <select name="reduc_type" class="custom-select custom-select-sm" id="reduc_type">
                        <option value="Par décompte">PAR DECOMPTE</option>
                        <option value="Par défaut">PAR DEFAUT</option>
                    </select>
                    <div id="contentCause">
                        <label for="reduc_cause" class="mt-2 font-weight-bold">Raison/Cause</label>
                        <input type="text" name="reduc_cause" id="reduc_cause" class="form-control" placeholder=". . .">
                    </div>  
                </div>
                <script>
                    $("#contentCause").hide()
                </script>
                `,
            type:"orange",
            theme:"modern",
            buttons:{
                btn1:{
                    text: 'Annuler',
                    action: function(){}
                },
                btn2:{
                    text: 'Valider',
                    btnClass: 'btn-orange',
                    keys: ['enter', 'shift'],
                    action: function(){
                        var  quantite_restant = $("#quantite_restant").val() ;

                        if(quantite_restant == "" || $("#reduc_qte").val() <= 0)
                        {
                            $.alert({
                                title: "Message",
                                content: "Erreur. Veuillez annuler l'opération",
                                type:'red'
                            }) ;

                            return false ;
                        }

                        self.closest('tr').find(".contentBtnReduire").html(`
                            <span class="text-warning">
                                Qte déduit : <b>`+$("#reduc_qte").val()+`</b>,
                                Type : <b>`+$("#reduc_type").val()+`</b>
                                <input type="hidden" name="reduc_val_entrepot[]" value="`+self.data("value")+`">
                                <input type="hidden" name="reduc_val_qte[]" value="`+$("#reduc_qte").val()+`">
                                <input type="hidden" name="reduc_val_type[]" value="`+$("#reduc_type").val()+`">
                                <input type="hidden" name="reduc_val_cause[]" value="`+$("#reduc_cause").val()+`">
                            </span>
                        `)
                    }
                }
            }
        })
        return false ;
    }) ;



    $(document).on("keyup","#reduc_qte",function(){
        var reduction = $(this).val() == "" ? 0 : $(this).val() ;
        var quantite_stock = parseFloat($("#quantite_stock").val()) ;
        var quantite_restant = quantite_stock - reduction ;

        if(quantite_restant < 0)
        {
            $.alert({
                title: "Message",
                content: "Le stock restant ne doit pas être négaitf.",
                type:'red'
            }) ;

            $(this).val("") ;

            return false ;
        }

        $("#quantite_restant").val(quantite_restant) ;
    }) ;

    $(document).on('change',"#reduc_type",function(){
        if($(this).val() == "Par défaut")
        {
            $("#contentCause").show()
        }
        else
        {
            $("#contentCause").hide()
        }
    })

    $(document).on("click",".stock_supprimer_produit",function(){
      var self = $(this)
      $.confirm({
          title: "Suppression",
          content:"Êtes-vous sûre de vouloir supprimer ?",
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
                          url: routes.stock_general_produit_delete, 
                          type:'post',
                          cache: false,
                          data:{idProduit:self.data("value")},
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
                                              location.assign(routes.stock_general) ;
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
    })

    var dataPrixs = [
        {
            selector : "#modif_inpt_prix_achat",
            action : "keyup"
        },
        {
            selector : "#modif_inpt_charge",
            action : "keyup"
        },
        {
            selector : "#modif_inpt_marge_type",
            action : "change"
        },
        {
            selector : "#modif_inpt_marge_valeur",
            action : "keyup"
        }
    ] ;

    dataPrixs.forEach(dataPrix => {
        $(document).on(dataPrix.action,dataPrix.selector,function(){
            calculPrixDeVente() ;
        })
    });

    function calculPrixDeVente()
    {
        var modif_inpt_prix_achat = $("#modif_inpt_prix_achat").val() == "" ? 0 : parseFloat($("#modif_inpt_prix_achat").val()) ;
        var modif_inpt_charge = $("#modif_inpt_charge").val() == "" ? 0 : parseFloat($("#modif_inpt_charge").val()) ;
        var modif_inpt_marge_type = $("#modif_inpt_marge_type").find("option:selected") ;
        var modif_inpt_marge_valeur = $("#modif_inpt_marge_valeur").val() == "" ? 0 : parseFloat($("#modif_inpt_marge_valeur").val()) ;
        
        var prixRevient = modif_inpt_prix_achat + modif_inpt_charge ;
        var prixVente = 0 ;
        
        if(modif_inpt_marge_type.data("calcul") == 1)
        {
            prixVente = prixRevient + modif_inpt_marge_valeur ;
        }
        else if(modif_inpt_marge_type.data("calcul") == 100)
        {
            prixVente = prixRevient + ((prixRevient * modif_inpt_marge_valeur) / 100) ;
        }
        else if(modif_inpt_marge_type.data("calcul") == -1)
        {
            prixVente = prixRevient * modif_inpt_marge_valeur ;
        }
        
        $("#modif_inpt_prix").val(prixVente) ;
    }
})