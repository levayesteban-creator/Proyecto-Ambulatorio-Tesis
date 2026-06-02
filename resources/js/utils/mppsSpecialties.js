/**
 * Catálogo MPPS — Referencia / Contrarreferencia (códigos 1–39).
 * Fuente: planilla oficial Consultorio Popular.
 */
export const MPPS_SPECIALTIES = [
  { code: 1, name: 'Odontología' },
  { code: 2, name: 'Oftalmología' },
  { code: 3, name: 'Traumatología y Ortopedia' },
  { code: 4, name: 'ORL' },
  { code: 5, name: 'Pediatría' },
  { code: 6, name: 'Medicina Interna' },
  { code: 7, name: 'Dermatología' },
  { code: 8, name: 'Cirugía' },
  { code: 9, name: 'Nutrición' },
  { code: 10, name: 'Neumonología' },
  { code: 11, name: 'Ginecología' },
  { code: 12, name: 'Patología de Cuello' },
  { code: 13, name: 'Patología de Mama' },
  { code: 14, name: 'Obstetricia' },
  { code: 15, name: 'Cardiología' },
  { code: 16, name: 'Nefrología' },
  { code: 17, name: 'Salud Mental' },
  { code: 18, name: 'Endocrinología' },
  { code: 19, name: 'Neurología' },
  { code: 20, name: 'Atención Psiquiátrica' },
  { code: 21, name: 'Hospitalización psiquiátrica' },
  { code: 22, name: 'Comunidad terapéutica' },
  { code: 23, name: 'Programas Sociales' },
  { code: 24, name: 'Educación Especial ME' },
  { code: 25, name: 'Rehabilitación' },
  { code: 26, name: 'Médico de Familia' },
  { code: 27, name: 'Reumatología' },
  { code: 28, name: 'Oncología' },
  { code: 29, name: 'Urología' },
  { code: 30, name: 'Gastroenterología' },
  { code: 31, name: 'Psicología' },
  { code: 32, name: 'Infectología' },
  { code: 33, name: 'Cirugía Cardiovascular' },
  { code: 34, name: 'Hematología' },
  { code: 35, name: 'Neurología' },
  { code: 36, name: 'Radiodiagnóstico' },
  { code: 37, name: 'Toxicología' },
  { code: 38, name: 'Alergólogo' },
  { code: 39, name: 'Optometrista' },
]

export function specialtyNameByCode(code) {
  return MPPS_SPECIALTIES.find((s) => s.code === code)?.name ?? `Especialidad ${code}`
}
