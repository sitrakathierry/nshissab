$(document).ready(function(){
    var produit_details_editor = new LineEditor(".produit_details_editor") ;

    produit_details_editor.setEditorText($(".produit_details_editor").text())
    $(".mybarCode").html("")

    $(".mybarCode").barcode(
        {
            code: $(".barcode_produit").val(),
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
        text: $(".qr_code_produit").val(),
    });
})