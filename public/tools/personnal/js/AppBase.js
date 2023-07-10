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

    getMonthName(monthIndex){
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

    str_pad(str, length, padChar, padType) {
      str = String(str); // Convertir en chaîne de caractères
    
      if (str.length >= length) {
        return str; // Pas besoin de remplissage
      }
    
      padChar = String(padChar); // Convertir en chaîne de caractères
    
      if (padChar.length === 0) {
        return str; // Pas de caractère de remplissage
      }
    
      padType = padType || 'right'; // Type de remplissage par défaut est "right"
    
      var padLength = length - str.length;
      var pad = padChar.repeat(padLength);
    
      if (padType === 'left') {
        return pad + str;
      } else if (padType === 'both') {
        var padLeftLength = Math.floor(padLength / 2);
        var padRightLength = padLength - padLeftLength;
        var padLeft = padChar.repeat(padLeftLength);
        var padRight = padChar.repeat(padRightLength);
        return padLeft + str + padRight;
      } else {
        return str + pad;
      }
    }

    convertirFormatDate(dateString) {
      // Séparer la date en jour, mois et année
      var dateParts = dateString.split('/');
      var jour = dateParts[0];
      var mois = dateParts[1];
      var annee = dateParts[2];
    
      // Créer un nouvel objet Date avec le format aaaa-mm-jj
      var date = new Date(annee, mois - 1, jour);
    
      // Obtenir les composants de la date au format 'aaaa-mm-jj'
      var anneeConvertie = date.getFullYear();
      var moisConverti = ('0' + (date.getMonth() + 1)).slice(-2); // Ajouter un zéro devant si nécessaire
      var jourConverti = ('0' + date.getDate()).slice(-2); // Ajouter un zéro devant si nécessaire
    
      // Retourner la date convertie au format 'aaaa-mm-jj'
      return anneeConvertie + '-' + moisConverti + '-' + jourConverti;
    }


    calculerDateApresNjours(dateInitiale, nbJours) {
      // Convertir la date initiale en objet Date
      var date = new Date(this.convertirFormatDate(dateInitiale));
      // Calculer la date après le nombre de jours spécifié
      var dateApresNJours = new Date(date.getTime() + (nbJours * 24 * 60 * 60 * 1000));
    
      // Conversion de la date en format souhaité (jj/mm/aaaa)
      var jour = dateApresNJours.getDate();
      var mois = dateApresNJours.getMonth() + 1; // Les mois commencent à partir de zéro (0)
      var annee = dateApresNJours.getFullYear();
    
      // Formattage de la date
      var dateFormatee = this.str_pad(jour,2,"0","left") + '/' + this.str_pad(mois,2,"0","left") + '/' + annee;
    
      return dateFormatee;
    }

    calculerDureeEnJours(date, mois) {
      var parts = date.split('/');
      var jour = parseInt(parts[0]);
      var moisDebut = parseInt(parts[1]) - 1; // Les mois commencent à partir de zéro dans les objets Date
      var annee = parseInt(parts[2]);
      
      var dateDebut = new Date(annee, moisDebut, jour);
      var dateFin = new Date(dateDebut.getFullYear(), dateDebut.getMonth() + mois, 0);
      
      var finJour = this.str_pad(dateFin.getDate(),2,"0","left") ;
      var finMois = this.str_pad((dateFin.getMonth() + 1),2,"0","left") ;

      var dureeEnJours = Math.ceil((dateFin - dateDebut) / (1000 * 60 * 60 * 24));
      var dateFinText = finJour +"/"+finMois+"/"+dateFin.getFullYear() ; 
      return (dureeEnJours+1)+"&##&"+dateFinText ;
    }
}