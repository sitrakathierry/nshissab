class AppBase
{
    constructor()
    {
        
    }

    checkData(elements)
      {
        var message = ""
        var type = ""
        var allow = true ;
        for (let i = 0; i < elements.length; i++) {
            const element = elements[i];
            if($(element.selector).val() == "")
            {
                message = element.title+" vide"
                type = element.type
                allow = false
                break ;
            }
        }

        if(!allow)
        {
            $.alert({
                title: "Message",
                content: message,
                type:type
            })
        }

        return allow
      }

    searchElement(resultContent, url, tableSearch, nbColumn)
    {
        
    }

    getMonthName(monthIndex) {
        var monthNames = [
          "Janvier",
          "Février",
          "Mars",
          "Avril",
          "Mai",
          "Juin",
          "Juillet",
          "Août",
          "Septembre",
          "Octobre",
          "Novembre",
          "Décembre"
        ];
      
        return monthNames[monthIndex];
      }

    getItemsDate(index)
    {
        var currentDate = new Date();
        var currentYear = currentDate.getFullYear();
        var optionMonth = ''
        for (var i = 0; i < 12; i++) {
            optionMonth += '<option value="'+(i+1)+'">'+this.getMonthName(i).toUpperCase()+'</option>'
          }

        var items = {
          DT:`
          <div class="col-md-3">
              <label for="date_specifique" class="mt-2 font-weight-bold text-uppercase">Date</label>
              <div class="input-group mb-3">
              <div class="input-group-prepend">
                  <span class="input-group-text" id="basic-addon1"><i class="fa fa-calendar"></i></span>
              </div>
                  <input type="text" class="form-control" placeholder=". . ." id="date_specifique" name="date_specifique">
              </div>
          </div>
          <script>
              $("#date_specifique").datepicker()
          </script>
          `,
          DD:`
          <div class="col-md-3">
              <label for="date_fourchette_debut" class="mt-2 font-weight-bold text-uppercase">Date Début</label>
              <div class="input-group mb-3">
              <div class="input-group-prepend">
                  <span class="input-group-text" id="basic-addon1"><i class="fa fa-calendar"></i></span>
              </div>
                  <input type="text" class="form-control" placeholder=". . ." id="date_fourchette_debut" name="date_fourchette_debut">
              </div>
          </div>
          <script>
              $("#date_fourchette_debut").datepicker()
          </script>
          `,
          DF:`
          <div class="col-md-3">
              <label for="date_fourchette_fin" class="mt-2 font-weight-bold text-uppercase">Date fin</label>
              <div class="input-group mb-3">
                  <div class="input-group-prepend">
                      <span class="input-group-text" id="basic-addon1"><i class="fa fa-calendar"></i></span>
                  </div>
                  <input type="text" class="form-control" placeholder=". . ." id="date_fourchette_fin" name="date_fourchette_fin">
              </div>
          </div>
          <script>
              $("#date_fourchette_fin").datepicker()
          </script>
          `,
          AN:`
          <div class="col-md-3">
              <label for="date_annee" class="mt-2 font-weight-bold text-uppercase">Année</label>
              <input type="number" name="date_annee" id="date_annee" class="form-control" value="`+currentYear+`" placeholder=". . .">
          </div>
          `,
          MS:`
          <div class="col-md-3">
              <label for="date_mois" class="mt-2 font-weight-bold text-uppercase">Mois</label>
              <select name="date_mois" class="custom-select chosen_select custom-select-sm" id="date_mois">
                  <option value="">-</option>
                  `+optionMonth+`
              </select>
          </div>
          <script>
              $(".chosen_select").chosen({
                  no_results_text: "Aucun resultat trouvé : "
              });
          </script>
          `
      }

      return items[index] ;
    }

    verificationElement(data = [], dataMessage = []) {
        let allow = true;
        let type = "green";
        let message = "Information enregistrée avec succès";
      
        for (let i = 0; i < data.length; i++) {
          if (!this.isNumeric(data[i])) {
            if (data[i] === "") {
              allow = false;
              type = "orange";
              message = dataMessage[i] + " vide";
              break;
            }
          } else {
            if (data[i] === "") {
              allow = false;
              type = "orange";
              message = dataMessage[i] + " vide";
              break;
            } else if (parseInt(data[i]) < 0) {
              allow = false;
              type = "red";
              message = dataMessage[i] + " doit être supérieur à 0";
              break;
            }
          }
        }
      
        let result = {};
        result.allow = allow;
        result.type = type;
        result.message = message;
      
        return result;
      }
      
      isNumeric(value) {
        return !isNaN(parseFloat(value)) && isFinite(value);
      }
}