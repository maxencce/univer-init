import { Controller } from "@hotwired/stimulus";

import { UniverSheetsCorePreset } from "@univerjs/preset-sheets-core";
import sheetsCoreFR from "@univerjs/preset-sheets-core/lib/locales/fr-FR";
import { createUniver, LocaleType, mergeLocales } from "@univerjs/presets";
import { WORKBOOK_DATA } from "@/controllers/data";

import "@univerjs/preset-sheets-core/lib/index.css";
import "@/styles/app.css";

export default class extends Controller {
  connect() {
    console.log("Univer Stimulus controller connecté !");

    // Crée l’instance Univer avec le preset tableur uniquement
    const { univerAPI } = createUniver({
      locale: LocaleType.FR_FR,
      locales: {
        [LocaleType.FR_FR]: mergeLocales(sheetsCoreFR),
      },
      presets: [
        UniverSheetsCorePreset({
          container: this.element as HTMLElement, // Le div du controller Stimulus sert de container
          toolbar: false,
          footer: false,
          formulaBar: false,
          contextMenu: false,
        }),
        
      ],
    });

    // Crée le classeur à partir des données
    univerAPI.createWorkbook(WORKBOOK_DATA);

    univerAPI.getActiveWorkbook.apply
  }
}