$(document).ready(function(){
    $("#agd_echance").hide()

    $(document).on('click',".agd_btn_ajouter", function(){
        var agd_ech_date = $("#agd_ech_date").val()
        var agd_ech_montant = $("#agd_ech_montant").val()

        var data = [
            agd_ech_date,
            agd_ech_montant,
        ]

        var dataMessage = [
            "Date échéance",
            "Montant échéance",
        ]
        var vide = false ;
        var caption = "" ;
        for (let i = 0; i < data.length; i++) 
        {
            const elem = data[i] ;
            if(elem == "")
            {
                vide = true ;
                caption = dataMessage[i] ;
                break ;
            }
        }

        if(vide)
        {
            $.alert({
                title: 'Champ vide',
                content: caption+" est vide",
                type:'orange',
            })
            return false;
        }
        
        var element = `
            <tr>
                <td>
                    `+agd_ech_date+`
                    <input type="hidden" name="agd_ech_enr_date[]" value="`+agd_ech_date+`">
                </td>
                <td>
                    `+agd_ech_montant+`
                    <input type="hidden" name="agd_ech_enr_montant[]" value="`+agd_ech_montant+`">
                </td>
                <td class="align-middle text-center">
                    <button type="button" class="btn agd_ech_suppr_ligne btn-sm btn-outline-danger font-smaller"><i class="fa fa-times"></i></button>
                </td>
            </tr>
        ` 
        $(".elem_echeance").append(element)

        $("#agd_ech_date").val("")
        $("#agd_ech_montant").val("")
    })

    $(document).on('click','.agd_ech_suppr_ligne',function(){
        $(this).closest('tr').remove()
    })
})