$(document).ready(function(){
    var instance = new Loading(files.loading)
    var modele_editor = new LineEditor("#modele_editor") ;
    modele_editor.setEditorText($("#modele_editor").val())
    $("#mod_image_origine_left").val($(".modele_image_left img").attr("src"))
    $("#mod_image_origine_right").val($(".modele_image_right img").attr("src"))

    var imageOrigineLeft = $(".modele_image_left img").attr("src")
    var imageOrigineRight = $(".modele_image_right img").attr("src")

    var parentLeft = $(".parentImgLeft")
    var imgL = new Image();
    imgL.src = imageOrigineLeft;
    imgL.onload = function() {
        parentLeft.find(".mod_val_largeur").val(imgL.width)
        parentLeft.find(".mod_val_hauteur").val(imgL.height)
    };

    var parentRight = $(".parentImgRight")
    var imgR = new Image();
    imgR.src = imageOrigineRight;
    imgR.onload = function() {
        parentRight.find(".mod_val_largeur").val(imgR.width)
        parentRight.find(".mod_val_hauteur").val(imgR.height)
    };

    $("#formUpdateModele").submit(function(){
        var self = $(this)
        console.log($(".Editor-editor").html())
        $.confirm({
            title: "Confirmation",
            content:"Êtes-vous sûre ?",
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
                        $.ajax({
                            url: routes.param_modele_pdf_save,
                            type:'post',
                            cache: false,
                            data:{
                                mod_id_modele: $("#mod_id_modele").val(),
                                modele_nom: $("#modele_nom").val(),
                                modele_value: $("#modele_value").val(),
                                data_modele_image_left: $("#data_modele_image_left").val(),
                                data_modele_image_right: $("#data_modele_image_right").val(),
                                forme_modele_pdf: $("#forme_modele_pdf").val(),
                                modele_editor: $(".Editor-editor").html(),
                            },
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
                                                $("input,select").val("")
                                                $(".chosen_select").trigger("chosen:updated")
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
})