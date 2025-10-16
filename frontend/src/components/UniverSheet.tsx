import { useEffect } from "react";
import { createUniver, LocaleType, mergeLocales } from "@univerjs/presets";
import { UniverSheetsCorePreset } from "@univerjs/preset-sheets-core";
import UniverPresetSheetsCoreFR from "@univerjs/preset-sheets-core/locales/fr-FR";
import "@univerjs/preset-sheets-core/lib/index.css";

export default function UniverSheet() {
    useEffect(() => {
        // Initialiser Univer une seule fois après le montage du composant
        const { univer, univerAPI } = createUniver({
            locale: LocaleType.FR_FR,
            locales: {
                [LocaleType.FR_FR]: mergeLocales(UniverPresetSheetsCoreFR),
            },
            presets: [
                UniverSheetsCorePreset({
                    container: "univer-container", // doit correspondre à ton div ci-dessous
                }),
            ],
            
            
        });

        univerAPI.createWorkbook({});
    }, []);

    return (
        <div
            id="univer-container"
            className="w-full h-[90vh] bg-gray-50 rounded-lg overflow-hidden shadow"
        />
    );
}
