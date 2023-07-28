const CONSTANTS = {
  PTOUCH_MODE: new Uint8Array([0x1b, 0x69, 0x61, 0x03]),
  INITIALIZE: new Uint8Array([0x5e, 0x49, 0x49]),
  FF: new Uint8Array([0x5e, 0x46, 0x46]),
};

// Exportez les constantes pour pouvoir les utiliser dans d'autres fichiers.
window.CONSTANTS = CONSTANTS;