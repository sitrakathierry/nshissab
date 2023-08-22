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

    
    for (let j = 1; j <= $(".elemMoisDepense").length; j++) {
        toolTipSelector(".elemModePaiement_"+j+" div","#ttpPaiement_"+j+"_") ;
        toolTipSelector(".elemMotif_"+j+" div","#ttpMotif_"+j+"_") ;
        toolTipSelector(".elemDepense_"+j+" tr","#ttpStatut_"+j+"_") ;
    }

})