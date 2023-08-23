$(document).ready(function(){
    function toolTipSelector(parent,element)
    {
        for (let i = 1; i <= $(parent).length ; i++) {
            $(element+i).easyTooltip({
              content: '<div class="text-white font-weight-bold text-uppercase text-center">'+$(element+i).data("content")+'</div>',
              defaultRadius: "3px",
              tooltipFtSize: "12px",
              tooltipZindex: 1000,
              tooltipPadding: "10px 15px",
              tooltipBgColor: "rgba(0,0,0,0.85)",
            })
        }
    }

    for (let j = 1; j <= $(".elemMoisJournal").length; j++) {
        toolTipSelector(".elemModePaiement_"+j+" div","#ttpPaiement_"+j+"_") ;
        toolTipSelector(".choixJournal","#ttpJournal_"+j+"_") ;
        // toolTipSelector(".elemDepense_"+j+" tr","#ttpStatut_"+j+"_") ;
    }

    $(document).on('mouseenter',".toggleIcon",function(){
        $(this).find("i").addClass("rotateIcon")
    })

    $(document).on("mouseleave",".toggleIcon",function(){
        $(this).find("i").removeClass("rotateIcon")
    })
})