<script lang="ts">
  import { fade } from "svelte/transition";
  import Swal from "sweetalert2";
  import CalendarCell from "./CalendarCell.svelte";
  import TeacherSelector from "./TeacherSelector.svelte";
  import Loader from "./Loader.svelte";
  import AdminStats from "./AdminStats.svelte";
  import SyncNotification from "./SyncNotification.svelte";
  import PieChart from "./PieChart.svelte";
  import { sheetsService } from "./services/google_sheets_service.svelte";
  import { festivos } from "./festivos";
  import { authUser, docenteName } from "../../lib/authStore";
  import { theme } from "../../lib/themeStore";
  import eieLogo from "../../assets/eie.png";
  import DriveFolderPicker from "../DriveFolderPicker.svelte";
  import { gdriveService } from "../../lib/gdriveService";
  import { GOOGLE_CLIENT_ID } from "../../constants";
  import { CloudUpload, FileSpreadsheet } from "@lucide/svelte";
  import ExcelJS from "exceljs";

  // Props para integración con inasistig
let { onBack } = $props()

  // Meses - declarado primero
  const monthNames = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
  const defaultMonthName = monthNames[new Date().getMonth()];

  // Usar auth de inasistig
  let email = $derived($authUser?.email || "");
  let teacherName = $derived($docenteName || "");
  const initialTeacher = $derived($docenteName || "");
  let month = $state(defaultMonthName);
  let hoursData = $state<Record<number, string>>({});
  let isSaving = $state(false);
  let isLoadingData = $state(false);
let showAdminStats = $state(false);
  let existingRecordIndex: number | null = null;
  let saveStatus = $state<"saved" | "saving" | "error" | "idle">("idle");
  let saveTimeout: any;
  let notificationConfig = $state({
    show: false,
    message: "",
    type: "saving" as "saving" | "saved" | "error" | "info",
  });
  let backendData = $state<Record<string, number>>({});
  let passwordValidatedMonths = $state<Record<string, boolean>>({});
  
  // Drive Export states
  let showDrivePicker = $state(false);
  let isSavingToDrive = $state(false);

  const categories = [
    {
      id: "normal",
      label: "Trabajo Normal",
      shortLabel: "Normal",
      color: "bg-blue-500",
      icon: "💼",
    },
    {
      id: "ordinario",
      label: "Permiso Ordinario",
      shortLabel: "Ordinario",
      color: "bg-green-500",
      icon: "📝",
    },
    {
      id: "sindical",
      label: "Permiso Sindical",
      shortLabel: "Sindical",
      color: "bg-purple-500",
      icon: "⚖️",
    },
    {
      id: "jurado",
      label: "Jurado de Votación",
      shortLabel: "Jurado",
      color: "bg-emerald-600",
      icon: "🗳️",
    },
    {
      id: "gobernacion",
      label: "Día Libre Gobernación",
      shortLabel: "Gobernación",
      color: "bg-cyan-600",
      icon: "🏛️",
    },
    {
      id: "profesor",
      label: "Día del Profesor",
      shortLabel: "Profesor",
      color: "bg-rose-500",
      icon: "🍎",
    },
    {
      id: "calamidad",
      label: "Calamidad Doméstica",
      shortLabel: "Calamidad",
      color: "bg-stone-500",
      icon: "🏠",
    },
    {
      id: "luto",
      label: "Licencia por Luto",
      shortLabel: "Luto",
      color: "bg-slate-600",
      icon: "✝️",
    },
    {
      id: "medica",
      label: "Incapacidad Médica",
      shortLabel: "Médica",
      color: "bg-red-500",
      icon: "🏥",
    },
    {
      id: "maternidad",
      label: "Licencia Mat./Pat.",
      shortLabel: "Licencia",
      color: "bg-teal-500",
      icon: "👶",
    },
    {
      id: "secretaria",
      label: "Permisos/Capacitaciones Secretaría",
      shortLabel: "Secretaría",
      color: "bg-amber-500",
      icon: "🎓",
    },
    {
      id: "bienestar",
      label: "Bienestar Docente",
      shortLabel: "Bienestar",
      color: "bg-pink-500",
      icon: "🧘",
    },
    {
      id: "pedagogica",
      label: "Jornada Pedagógica",
      shortLabel: "Pedagógica",
      color: "bg-violet-500",
      icon: "📓",
    },
    {
      id: "familia",
      label: "Día de la Familia",
      shortLabel: "Familia",
      color: "bg-orange-500",
      icon: "👨‍👩‍👧‍👦",
    },
    {
      id: "cumpleaños",
      label: "Cumpleaños",
      shortLabel: "Cumpleaños",
      color: "bg-yellow-500",
      icon: "🎂",
    },
    {
      id: "quinquenio",
      label: "Quinquenio",
      shortLabel: "Quinquenio",
      color: "bg-indigo-600",
      icon: "🏅",
    },
    {
      id: "vacaciones",
      label: "Vacaciones",
      shortLabel: "Vacaciones",
      color: "bg-sky-500",
      icon: "✈️",
    },
    {
      id: "paro",
      label: "Paro / Huelga",
      shortLabel: "Paro",
      color: "bg-red-700",
      icon: "📢",
    },
    {
      id: "extra",
      label: "Jornada Extra",
      shortLabel: "Extra",
      color: "bg-orange-700",
      icon: "💪",
    },
    {
      id: "sabdomfest",
      label: "Sábados, Domingos y Festivos",
      shortLabel: "SDF",
      color: "bg-slate-400",
      icon: "🏝️",
    },
  ];

  const INSTITUTION_NAME = "INSTITUTO GUÁTICA";
  const currentDate = new Date();
  const currentYear = currentDate.getFullYear();
  const currentMonthNum = currentDate.getMonth(); // 0-11
  const currentDay = currentDate.getDate();

  function getDaysInMonth(monthName: string) {
    const monthIndex = monthNames.indexOf(monthName);
    if (monthIndex === -1) return 0;
    return new Date(currentYear, monthIndex + 1, 0).getDate();
  }

  function getStartDay(monthName: string) {
    const monthIndex = monthNames.indexOf(monthName);
    if (monthIndex === -1) return 0;
    return (new Date(currentYear, monthIndex, 1).getDay() + 6) % 7;
  }

  function isValidMonthToSave(selectedMonth: string): { valid: boolean; message: string; needsPassword: boolean } {
    const selectedMonthIndex = monthNames.indexOf(selectedMonth);
    
    if (selectedMonthIndex === -1) {
      return { valid: false, message: "Mes inválido", needsPassword: false };
    }

    // Future monthNames - not valid
    if (selectedMonthIndex > currentMonthNum) {
      return { valid: false, message: "No se puede guardar meses futuros", needsPassword: false };
    }

    // Current month - always valid
    if (selectedMonthIndex === currentMonthNum) {
      return { valid: true, message: "", needsPassword: false };
    }

    // Previous month - valid only in first 15 days of current month
    if (selectedMonthIndex === currentMonthNum - 1) {
      if (currentDay <= 15) {
        return { valid: true, message: "", needsPassword: false };
      } else {
        return { valid: true, message: "", needsPassword: true };
      }
    }

    // Handle year boundary: January (0) to December (11) - previous month
    if (currentMonthNum === 0 && selectedMonthIndex === 11) {
      if (currentDay <= 15) {
        return { valid: true, message: "", needsPassword: false };
      } else {
        return { valid: true, message: "", needsPassword: true };
      }
    }

    // Months older than previous month - require password
    return { valid: true, message: "", needsPassword: true };
  }

  function generateExpectedPassword(monthName: string): string {
    const monthIndex = monthNames.indexOf(monthName);
    const monthNum = (monthIndex + 1).toString().padStart(2, '0');
    return `${currentDay}-${monthNum}`;
  }

  async function validatePasswordWithSwal(monthName: string): Promise<boolean> {
    const expectedPassword = generateExpectedPassword(monthName);
    
    const inputId = `swal-password-${Date.now()}`;
    
    const { value: password } = await Swal.fire({
      title: '🔒 Acceso Restringido',
      html: `
        <p class="text-sm text-slate-600 mb-4">
          El mes "${monthName}" requiere autorización especial.<br>
          Ingrese la contraseña proporcionada por el administrador.
        </p>
        <input 
          type="password" 
          id="${inputId}" 
          class="swal2-input" 
          placeholder=""
          autocomplete="new-password"
        >
      `,
      willOpen: () => {
        document.querySelectorAll('input[id^="swal-password-"]').forEach((el) => {
          (el as HTMLInputElement).value = '';
        });
      },
      preConfirm: () => {
        const input = document.getElementById(inputId) as HTMLInputElement;
        return input?.value;
      },
      showCancelButton: true,
      confirmButtonText: 'Validar',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#2563eb',
      allowOutsideClick: false,
    });

    if (password === expectedPassword) {
      passwordValidatedMonths[monthName] = true;
      await Swal.fire({
        icon: 'success',
        title: '✅ Acceso Concedido',
        text: `Ha validado correctamente el mes de ${monthName}`,
        confirmButtonColor: '#2563eb',
        timer: 2000,
        showConfirmButton: false,
      });
      return true;
    } else if (password) {
      await Swal.fire({
        icon: 'error',
        title: '❌ Contraseña Incorrecta',
        text: 'La contraseña ingresada no es válida. Contacte al administrador.',
        confirmButtonColor: '#2563eb',
      });
    }
    return false;
  }

  const daysInMonth = $derived(getDaysInMonth(month));
  const startDay = $derived(getStartDay(month));

function handleSelect(day: number, category: string) {
    console.log("DEBUG: handleSelect called", { day, category, currentData: $state.snapshot(hoursData) });
    
    const monthValidation = isValidMonthToSave(month);
    if (!monthValidation.valid) {
      saveStatus = "error";
      notificationConfig = {
        show: true,
        message: `❌ ${monthValidation.message}`,
        type: "error",
      };
      console.log("DEBUG: Month validation failed in handleSelect:", monthValidation.message);
      return;
    }
    
    if (monthValidation.needsPassword && !passwordValidatedMonths[month]) {
      validatePasswordWithSwal(month).then((isValid) => {
        if (isValid) {
          if (category === "") {
            delete hoursData[day];
          } else {
            hoursData[day] = category;
          }
          console.log("DEBUG: After password validation update, hoursData:", $state.snapshot(hoursData));
          triggerAutoSave();
        }
      });
      return;
    }
    
    if (category === "") {
      delete hoursData[day];
    } else {
      hoursData[day] = category;
    }
    console.log("DEBUG: After update, hoursData:", $state.snapshot(hoursData));
    triggerAutoSave();
  }

function triggerAutoSave() {
    console.log("DEBUG: triggerAutoSave called", { email, teacherName, month, hoursData: $state.snapshot(hoursData) });
    if (!email || !teacherName || !month) {
      console.log("DEBUG: Missing required fields, not saving");
      return;
    }

    // Validar que el mes sea permitido para guardar
    const monthValidation = isValidMonthToSave(month);
    if (!monthValidation.valid) {
      saveStatus = "error";
      notificationConfig = {
        show: true,
        message: `❌ ${monthValidation.message}`,
        type: "error",
      };
      console.log("DEBUG: Month validation failed:", monthValidation.message);
      return;
    }

    if (monthValidation.needsPassword && !passwordValidatedMonths[month]) {
      console.log("DEBUG: Month requires password validation, not saving");
      return;
    }

    saveStatus = "saving";
    notificationConfig = {
      show: true,
      message: "Sincronizando datos...",
      type: "saving",
    };
    if (saveTimeout) clearTimeout(saveTimeout);

    saveTimeout = setTimeout(async () => {
      try {
        console.log("DEBUG: Starting save process");
        const daysArray: string[] = [];
        for (let day = 1; day <= 31; day++) {
          daysArray.push(hoursData[day] || "");
        }

        if (daysArray.length !== 31) {
          throw new Error("Error interno: no se generaron 31 días.");
        }

        const timestamp = new Date().toLocaleString("es-CO", {
          timeZone: "America/Bogota",
        });

        const values = [timestamp, email, teacherName, month, ...daysArray];
        console.log("DEBUG: Values to save:", values);

        if (values.length !== 35) {
          throw new Error(
            `Payload inválido: se esperaban 35 elementos, pero se tienen ${values.length}.`,
          );
        }

        console.log("DEBUG: Calling sheetsService.appendRow");
        const result = await sheetsService.appendRow(
          values,
          existingRecordIndex,
        );
        console.log("DEBUG: Save result:", result);

        if (result.success) {
          saveStatus = "saved";
          notificationConfig = {
            show: true,
            message: "✅ Cambios guardados exitosamente",
            type: "saved",
          };

          const savedCategoryCount: Record<string, number> = {};
          Object.entries(hoursData).forEach(([day, categoryId]) => {
            if (categoryId) {
              savedCategoryCount[categoryId] =
                (savedCategoryCount[categoryId] || 0) + 1;
            }
          });
          backendData = savedCategoryCount;
          console.log("DEBUG: Updated backendData:", $state.snapshot(backendData));

          if (!result.updated && result.rowIndex) {
            existingRecordIndex = result.rowIndex;
          }
          setTimeout(() => {
            if (saveStatus === "saved") saveStatus = "idle";
          }, 3000);
        } else {
          console.log("DEBUG: Save failed:", result);
          saveStatus = "error";
          notificationConfig = {
            show: true,
            message: "❌ Error al guardar los cambios",
            type: "error",
          };
        }
      } catch (error) {
        console.error("Auto-save error:", error);
        saveStatus = "error";
        notificationConfig = {
          show: true,
          message: "❌ Error de conexión",
          type: "error",
        };
      }
    }, 1000);
  }

  // --- Export to Google Drive ---
  const handleExportToDrive = () => {
    if (!month || !teacherName) {
      Swal.fire({
        icon: "warning",
        title: "Faltan datos",
        text: "Seleccione un mes y docente antes de exportar",
        confirmButtonColor: "#6366f1",
      });
      return;
    }
    showDrivePicker = true;
  };

  const generarExcelMensual = async (): Promise<Blob> => {
    const workbook = new ExcelJS.Workbook();
    const ws = workbook.addWorksheet("Horas Laborables");
    
    const currentYear = new Date().getFullYear();
    const daysInMonth = new Date(currentYear, monthNames.indexOf(month) + 1, 0).getDate();
    
    // Título
    ws.mergeCells("A1:D1");
    const title = ws.getCell("A1");
    title.value = `HORAS LABORABLES - ${teacherName.toUpperCase()} - ${month.toUpperCase()} ${currentYear}`;
    title.font = { bold: true, size: 14, color: { argb: "FFFFFF" } };
    title.fill = { type: "pattern", pattern: "solid", fgColor: { argb: "2E75B6" } };
    title.alignment = { horizontal: "center" };
    
    // Información
    ws.addRow(["Docente:", teacherName]);
    ws.addRow(["Mes:", month]);
    ws.addRow(["Año:", currentYear]);
    ws.addRow([]);
    
    // Encabezados
    const headerRow = ws.addRow(["Día", "Categoría", "Horas", "Color"]);
    headerRow.font = { bold: true, color: { argb: "FFFFFF" } };
    headerRow.fill = { type: "pattern", pattern: "solid", fgColor: { argb: "4472C4" } };
    headerRow.alignment = { horizontal: "center" };
    
    // Mapeo de colores
    const colorMap: Record<string, string> = {
      "bg-blue-500": "3b82f6",
      "bg-green-500": "22c55e",
      "bg-purple-500": "a855f7",
      "bg-emerald-600": "059669",
      "bg-cyan-600": "0891b2",
      "bg-rose-500": "f43f5e",
      "bg-stone-500": "78716c",
      "bg-slate-600": "475569",
      "bg-red-500": "ef4444",
      "bg-teal-500": "14b8a6",
      "bg-amber-500": "f59e0b",
      "bg-pink-500": "ec4899",
      "bg-violet-500": "8b5cf6",
      "bg-orange-500": "f97316",
      "bg-yellow-500": "eab308",
      "bg-indigo-600": "4f46e5",
      "bg-sky-500": "0ea5e9",
      "bg-red-700": "b91c1c",
      "bg-slate-400": "94a3b8",
    };
    
    // Datos del mes
    const counts: Record<string, number> = {};
    for (let day = 1; day <= daysInMonth; day++) {
      const catId = hoursData[day];
      const cat = categories.find((c) => c.id === catId);
      const label = cat?.label || "(sin registro)";
      const horas = catId ? 1 : 0;
      const colorHex = cat ? colorMap[cat.color] || "94a3b8" : "e2e8f0";
      
      const row = ws.addRow([day, label, horas]);
      row.getCell(4).fill = { type: "pattern", pattern: "solid", fgColor: { argb: colorHex } };
      
      if (cat) {
        counts[cat.label] = (counts[cat.label] || 0) + 1;
      }
    }
    
    ws.addRow([]);
    
    // Resumen
    const resumenRow = ws.addRow(["RESUMEN"]);
    resumenRow.font = { bold: true, size: 12 };
    resumenRow.fill = { type: "pattern", pattern: "solid", fgColor: { argb: "E2E8F0" } };
    ws.mergeCells(`A${ws.rowCount}:D${ws.rowCount}`);
    
    Object.entries(counts).forEach(([catName, count]) => {
      const row = ws.addRow([catName, `${count} horas`, count]);
      row.font = { italic: true };
    });
    
    ws.addRow([]);
    const totalRow = ws.addRow(["TOTAL", "", Object.keys(hoursData).length]);
    totalRow.font = { bold: true };
    totalRow.fill = { type: "pattern", pattern: "solid", fgColor: { argb: "FEF3C7" } };
    
    // Ancho de columnas
    ws.getColumn(1).width = 8;
    ws.getColumn(2).width = 30;
    ws.getColumn(3).width = 10;
    ws.getColumn(4).width = 10;
    
    const buffer = await workbook.xlsx.writeBuffer();
    return new Blob([buffer], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
  };

  const handleDriveFolderSelect = async (folder: { id: string; name: string } | null) => {
    if (!folder) {
      showDrivePicker = false;
      return;
    }
    
    isSavingToDrive = true;
    try {
      const blob = await generarExcelMensual();
      const currentYear = new Date().getFullYear();
      const fileName = `Horas_${teacherName.replace(/ /g, "_")}_${month}_${currentYear}.xlsx`;
      
      const result = await gdriveService.uploadFile(
        blob,
        fileName,
        "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        GOOGLE_CLIENT_ID,
        folder.id
      );
      
      if (result.success) {
        await Swal.fire({
          icon: "success",
          title: "Guardado",
          text: `Archivo guardado en: ${folder.name}`,
          confirmButtonColor: "#10b981",
        });
      } else {
        await Swal.fire({
          icon: "error",
          title: "Error",
          text: result.error || "No se pudo guardar el archivo",
          confirmButtonColor: "#ef4444",
        });
      }
    } catch (error) {
      console.error("Error exporting to Drive:", error);
      await Swal.fire({
        icon: "error",
        title: "Error",
        text: "No se pudo generar el archivo",
        confirmButtonColor: "#ef4444",
      });
    } finally {
      isSavingToDrive = false;
      showDrivePicker = false;
    }
  };

  const completedDays = $derived(Object.keys(hoursData).length);
  const totalDays = $derived(daysInMonth || 30);
  const progress = $derived((completedDays / totalDays) * 100);

$effect(() => {
    if (teacherName && month) {
      loadExistingRecords();
    } else {
      hoursData = {};
      backendData = {};
    }
  });

async function loadExistingRecords() {
    isLoadingData = true;
    existingRecordIndex = null;
    backendData = {};
    try {
      const result = await sheetsService.getRows();
      if (result.success && result.records) {
        const normalizedTeacherName = normalizeString(teacherName);
        const existingRecord = result.records.find((record) => {
          const recordTeacher = record.values[2]?.trim();
          const recordMonth = record.values[3]?.trim();
          return (
            normalizeString(recordTeacher) === normalizedTeacherName &&
            recordMonth === month.trim()
          );
        });

        if (existingRecord) {
          existingRecordIndex = existingRecord.rowIndex;
          const loadedData: Record<number, string> = {};
          for (let i = 1; i <= 31; i++) {
            const val = existingRecord.values[i + 3];
            if (val) loadedData[i] = val;
          }
          hoursData = loadedData;

          const backendCategoryCountCalculated: Record<string, number> = {};
          Object.entries(loadedData).forEach(([day, categoryId]) => {
            if (categoryId) {
              backendCategoryCountCalculated[categoryId] = (backendCategoryCountCalculated[categoryId] || 0) + 1;
            }
          });
          backendData = backendCategoryCountCalculated;
        } else {
          console.log("No existing record found, auto-filling data");          const monthIndex = monthNames.indexOf(month);
          const autoFilledData: Record<number, string> = {};
          if (monthIndex !== -1) {
            const daysInMonthCount = new Date(
              currentYear,
              monthIndex + 1,
              0,
            ).getDate();
            for (let d = 1; d <= daysInMonthCount; d++) {
              const date = new Date(currentYear, monthIndex, d);
              const dayOfWeek = date.getDay();
              if (dayOfWeek === 0 || dayOfWeek === 6) {
                autoFilledData[d] = "sabdomfest";
              }
            }

            festivos.forEach((festivo) => {
              const [year, monthNum, day] = festivo.fecha
                .split("-")
                .map(Number);
              const holidayDate = new Date(year, monthNum - 1, day);
              if (
                holidayDate.getFullYear() === currentYear &&
                holidayDate.getMonth() === monthIndex
              ) {
                autoFilledData[holidayDate.getDate()] = "sabdomfest";
              }
            });
          }
hoursData = autoFilledData;
          console.log("Auto-filled hoursData:", autoFilledData);
        }
      }
    } catch (error) {
      console.error("Error cargando registros:", error);
    } finally {
      isLoadingData = false;
    }
  }

function handleOpenStats() {
    // Si hay datos en hoursData, mostrar el modal del calendario
    if (Object.keys(hoursData).length > 0) {
      const calendarContent = generateCalendarHTML();
      Swal.fire({
        title: `${teacherName || 'Docente'} - ${month || 'Mes'}`,
        html: calendarContent,
        width: '800px',
        confirmButtonText: 'Ver Estadísticas Globales',
        showConfirmButton: true,
        showCancelButton: true,
        cancelButtonText: 'Cerrar',
        confirmButtonColor: '#2563eb',
      }).then((result) => {
        if (result.isConfirmed) {
          showAdminStats = true;
        }
      });
    } else {
      // Si no hay datos, mostrar estadísticas globales
      showAdminStats = true;
    }
  }

  function generateCalendarHTML(): string {
    const monthIndex = monthNames.indexOf(month);
    const year = new Date().getFullYear();
    const daysInMonthCount = new Date(year, monthIndex + 1, 0).getDate();
    const startDay = (new Date(year, monthIndex, 1).getDay() + 6) % 7;
    
    let calendarHTML = `
      <div class="text-left">
        <div class="grid grid-cols-7 mb-2">
          <div class="text-center text-xs font-bold text-slate-400 uppercase py-2">Lun</div>
          <div class="text-center text-xs font-bold text-slate-400 uppercase py-2">Mar</div>
          <div class="text-center text-xs font-bold text-slate-400 uppercase py-2">Mié</div>
          <div class="text-center text-xs font-bold text-slate-400 uppercase py-2">Jue</div>
          <div class="text-center text-xs font-bold text-slate-400 uppercase py-2">Vie</div>
          <div class="text-center text-xs font-bold text-slate-400 uppercase py-2">Sáb</div>
          <div class="text-center text-xs font-bold text-slate-400 uppercase py-2">Dom</div>
        </div>
        <div class="grid grid-cols-7 gap-1">
    `;
    
    // Empty cells before first day
    for (let i = 0; i < startDay; i++) {
      calendarHTML += `<div></div>`;
    }
    
    // Days
    for (let day = 1; day <= daysInMonthCount; day++) {
      const categoryId = hoursData[day];
      const category = categoryId ? categories.find(c => c.id === categoryId) : null;
      
      if (category) {
        const colorMap: Record<string, string> = {
          'bg-blue-500': '#3b82f6',
          'bg-green-500': '#22c55e',
          'bg-purple-500': '#a855f7',
          'bg-emerald-600': '#059669',
          'bg-cyan-600': '#0891b2',
          'bg-rose-500': '#f43f5e',
          'bg-stone-500': '#78716c',
          'bg-slate-600': '#475569',
          'bg-red-500': '#ef4444',
          'bg-teal-500': '#14b8a6',
          'bg-amber-500': '#f59e0b',
          'bg-pink-500': '#ec4899',
          'bg-violet-500': '#8b5cf6',
          'bg-orange-500': '#f97316',
          'bg-yellow-500': '#eab308',
          'bg-indigo-600': '#4f46e5',
          'bg-sky-500': '#0ea5e9',
          'bg-red-700': '#b91c1c',
          'bg-slate-400': '#94a3b8',
        };
        const bgColor = colorMap[category.color] || '#94a3b8';
        calendarHTML += `
          <div class="aspect-square rounded-lg flex flex-col items-center justify-center text-xs font-medium text-white shadow-md"
               style="background-color: ${bgColor};" title="${category.label}">
            <span>${day}</span>
            <span class="text-[8px] opacity-90 hidden sm:block">${category.shortLabel}</span>
          </div>
        `;
      } else {
        calendarHTML += `
          <div class="aspect-square rounded-lg flex items-center justify-center text-xs font-medium bg-slate-100 text-slate-400">
            ${day}
          </div>
        `;
      }
    }
    
    calendarHTML += `</div></div>`;
    
    return calendarHTML;
  }

  const weekdays = ["Lun", "Mar", "Mié", "Jue", "Vie", "Sáb", "Dom"];

  function normalizeString(str: string): string {
    return str.trim().toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
  }

  // ✅ CORRECCIÓN CLAVE: uso correcto de $derived.by

</script>

<div class="max-w-[1600px] mx-auto p-3 sm:p-4 md:p-8" in:fade>
  <div class="flex flex-col lg:flex-row gap-4 sm:gap-6 lg:gap-8 items-start">
    <!-- Header Card (Sidebar on Desktop) -->
    <div class="w-full lg:w-[400px] lg:sticky lg:top-8 space-y-4 sm:space-y-6">
      <div
        class="hidden md:block absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-purple-100 rounded-full blur-3xl opacity-50"
      ></div>
      <div
        class="hidden md:block absolute bottom-0 left-0 -ml-16 -mb-16 w-64 h-64 bg-blue-100 rounded-full blur-3xl opacity-50"
      ></div>

      <div class="relative z-10 space-y-6">
        <!-- Back Button -->
        <button
          onclick={onBack}
          class="flex items-center gap-2 text-slate-600 hover:text-slate-900 transition-colors"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          <span class="font-medium">Regresar al Dashboard</span>
        </button>

        <div class="flex justify-between items-center w-full">
          <div class="flex items-center gap-4">
            <div class="relative group">
              <div
                class="absolute inset-0 bg-blue-500/20 rounded-full blur-lg group-hover:blur-xl transition-all duration-300"
              ></div>
              <img
                src={eieLogo}
                alt="Escudo Institucional"
                class="relative w-16 h-16 object-contain drop-shadow-md transform group-hover:scale-110 transition-transform duration-300"
              />
            </div>
            <div class="h-10 w-px bg-slate-200"></div>
            <p
              class="text-slate-500 font-bold tracking-widest uppercase text-[10px] leading-tight max-w-[150px]"
            >
              {INSTITUTION_NAME} <br />
              {currentYear}
            </p>
          </div>

          <button
            onclick={handleOpenStats}
            class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all"
            title="Ver Estadísticas"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="w-5 h-5"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
              />
            </svg>
          </button>
        </div>

        <div
          class="flex flex-col sm:flex-row lg:flex-col xl:flex-row sm:items-end lg:items-start xl:items-end justify-between gap-4"
        >
          <div class="space-y-1">
            <h1
              class="text-2xl sm:text-3xl lg:text-4xl font-bold bg-gradient-to-r from-slate-900 to-slate-700 bg-clip-text text-transparent leading-tight"
            >
              Control de Horas Laboradas
            </h1>
          </div>
          <div
            class="flex flex-row sm:flex-col lg:flex-row xl:flex-col items-baseline sm:items-end lg:items-baseline xl:items-end gap-2 sm:gap-0"
          >
            <div
              class="text-[10px] text-slate-500 font-extrabold uppercase tracking-widest"
            >
              Días registrados
            </div>
            <div
              class="text-2xl sm:text-3xl font-mono font-bold text-slate-900 leading-none"
            >
              {completedDays}
              <span class="text-slate-300 font-light mx-0.5">/</span>
              {totalDays}
            </div>
          </div>
        </div>

        <!-- Progress Bar -->
        <div class="h-2 w-full bg-slate-100 rounded-full overflow-hidden">
          <div
            class="h-full bg-gradient-to-r from-blue-600 to-purple-600 transition-all duration-500 ease-out"
            style="width: {progress}%"
          ></div>
        </div>

        <div class="grid grid-cols-1 gap-5">
          <!-- Email Input -->
          <div class="space-y-2">
            <label
              class="text-[10px] font-extrabold text-slate-600 uppercase tracking-widest pl-1"
              for="email">Correo Electrónico</label
            >
            <div class="relative group">
              <div
                class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-purple-600 transition-colors"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-5 w-5"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"
                  />
                </svg>
              </div>
              <input
                id="email"
                type="email"
                bind:value={email}
                placeholder="ejemplo@correo.com"
                class="w-full bg-white border border-slate-300 rounded-2xl pl-11 pr-4 py-3 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-4 focus:ring-purple-500/10 focus:border-purple-500 transition-all duration-300 shadow-sm hover:border-slate-400"
              />
            </div>
          </div>

          <!-- Teacher Selector -->
          <div class="space-y-2">
            <label
              class="text-[10px] font-extrabold text-slate-600 uppercase tracking-widest pl-1"
              for="teacher">Nombre Docente</label
            >
            <div class="relative group">
              <div
                class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-500 group-focus-within:text-purple-600 transition-colors z-20"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-5 w-5"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                  />
                </svg>
              </div>
              <TeacherSelector id="teacher" bind:value={teacherName} initialValue={initialTeacher} />
            </div>
          </div>

          <!-- Month Selector -->
          <div class="space-y-2">
            <label
              class="text-[10px] font-extrabold text-slate-600 uppercase tracking-widest pl-1"
              for="month">Mes de Registro</label
            >
            <div class="relative group">
              <div
                class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-500 group-focus-within:text-purple-600 transition-colors"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-5 w-5"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                  />
                </svg>
              </div>
<select
                id="month"
                bind:value={month}
                class="w-full bg-white border border-slate-300 rounded-2xl pl-11 pr-10 py-3 text-slate-900 focus:outline-none focus:ring-4 focus:ring-purple-500/10 focus:border-purple-500 transition-all duration-300 appearance-none cursor-pointer shadow-sm hover:border-slate-400"
              >
                {#each monthNames as m}
                  <option value={m}>{m}</option>
                {/each}
              </select>
              <div
                class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-500"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-4 w-4"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 9l-7 7-7-7"
                  />
                </svg>
              </div>
            </div>
          </div>

          <!-- Pie Chart -->
          <div class="mt-4">
            <h3 class="text-sm font-semibold text-slate-700 mb-2">
              Resumen de Actividades {teacherName && month ? `- ${month}` : ""}
            </h3>
            <!-- ✅ CORRECCIÓN: sin paréntesis -->
            <PieChart
              data={backendData}
              {categories}
              title={teacherName && month
                ? `${month} - ${teacherName}`
                : "Selecciona docente y mes"}
            />

            {#if !teacherName || !month}
              <div
                class="mt-2 p-3 bg-blue-50 border border-blue-200 rounded-lg text-center"
              >
                <p class="text-xs text-blue-700">
                  {!teacherName ? "Selecciona un docente" : "Selecciona un mes"}
                  para ver datos específicos
                </p>
              </div>
            {/if}
          </div>
        </div>
      </div>
    </div>

    <!-- Calendar Grid Card (Main Content) -->
    <div
      class="flex-1 w-full bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-xl"
    >
      <div class="p-4 md:p-6 border-b border-slate-100 bg-white">
        <div
          class="flex flex-col xl:flex-row gap-6 justify-between items-start xl:items-center"
        >
          <!-- Title Section -->
          <div class="shrink-0">
            <h2 class="text-xl font-bold text-slate-800 tracking-tight">
              {month ? `Calendario de ${month}` : "Seleccione un mes"}
            </h2>
            {#if !month}
              <p class="text-xs text-slate-500 mt-1 font-medium">
                Visualice y gestione los tipos de jornada
              </p>
            {/if}
          </div>

          <!-- Legend Section -->
          <div
            class="flex w-full xl:w-auto overflow-x-auto md:overflow-visible md:flex-wrap gap-2 pb-2 md:pb-0 justify-start xl:justify-end custom-scrollbar"
          >
            {#each categories as cat}
              <div
                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg border border-slate-100 bg-slate-50/50 hover:bg-slate-50 hover:border-slate-200 transition-all cursor-default group shrink-0"
              >
                <div
                  class="w-2 h-2 rounded-full {cat.color} shadow-sm group-hover:scale-110 transition-transform"
                ></div>
                <span
                  class="text-[10px] font-bold text-slate-600 uppercase tracking-wide group-hover:text-slate-800"
                >
                  {cat.label}
                </span>
              </div>
            {/each}
          </div>
        </div>
      </div>

      {#if month}
        <div
          class="hidden md:grid grid-cols-7 border-b border-slate-100 bg-slate-50/50"
        >
          {#each weekdays as day}
            <div
              class="py-3 text-center text-[10px] font-bold text-slate-400 uppercase tracking-widest"
            >
              {day}
            </div>
          {/each}
        </div>

        <div class="grid grid-cols-1 md:grid-cols-7">
          {#each Array(startDay) as _}
            <CalendarCell
              day={0}
              value=""
              onSelect={() => {}}
              categories={[]}
              isCurrentMonth={false}
            />
          {/each}

          {#each Array(daysInMonth) as _, i}
            {@const dayNum = i + 1}
            {@const weekdayIndex = (startDay + i) % 7}
            <CalendarCell
              day={dayNum}
              value={hoursData[dayNum] || ""}
              onSelect={(val) => handleSelect(dayNum, val)}
              {categories}
              weekday={weekdays[weekdayIndex]}
            />
          {/each}

          {#each Array((7 - ((startDay + daysInMonth) % 7)) % 7) as _}
            <CalendarCell
              day={0}
              value=""
              onSelect={() => {}}
              categories={[]}
              isCurrentMonth={false}
            />
          {/each}
        </div>
      {:else}
        <div class="p-20 text-center space-y-4">
          <div class="text-4xl">📅</div>
          <p class="text-slate-400 font-medium">
            Por favor seleccione un mes en el panel superior para habilitar el
            registro diario.
          </p>
        </div>
      {/if}

      <!-- Footer Action -->
      <div
        class="p-8 bg-slate-50 border-t border-slate-100 flex flex-col md:flex-row items-center justify-between gap-6"
      >
        <div class="flex flex-col gap-2">
          <div class="text-sm text-slate-500 max-w-md italic">
            Selecciona cada día para registrar tu actividad. Los cambios se
            guardan automáticamente.
          </div>
        </div>

        <div class="flex items-center gap-3">
          <button
            onclick={onBack}
            class="px-4 py-2 text-slate-600 bg-white border border-slate-300 rounded-lg font-medium hover:bg-slate-50 transition-colors"
          >
            Regresar
          </button>
          
          <button
            onclick={handleExportToDrive}
            class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white rounded-lg font-medium shadow-md hover:shadow-lg hover:from-emerald-600 hover:to-emerald-700 transition-all"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v1m-4-8l-4-4m0 0L8 4m4 4L12 4" />
            </svg>
            Guardar en Drive
          </button>
          {#if saveStatus === "saving"}
            <div
              class="flex items-center gap-2 text-blue-600 font-bold text-xs uppercase tracking-widest"
              in:fade
            >
              <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24">
                <circle
                  class="opacity-25"
                  cx="12"
                  cy="12"
                  r="10"
                  stroke="currentColor"
                  stroke-width="4"
                  fill="none"
                ></circle>
                <path
                  class="opacity-75"
                  fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                ></path>
              </svg>
              Sincronizando...
            </div>
          {:else if saveStatus === "saved"}
            <div
              class="flex items-center gap-2 text-emerald-600 font-bold text-xs uppercase tracking-widest"
              in:fade
            >
              <svg
                class="h-4 w-4"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="3"
                  d="M5 13l4 4L19 7"
                />
              </svg>
              Cambios Guardados
            </div>
          {:else if saveStatus === "error"}
            <div
              class="flex items-center gap-2 text-rose-600 font-bold text-xs uppercase tracking-widest"
              in:fade
            >
              <svg
                class="h-4 w-4"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="3"
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
              Error al Guardar
            </div>
          {/if}
        </div>
      </div>
    </div>
  </div>
</div>

{#if showAdminStats}
  <AdminStats
    onBack={() => (showAdminStats = false)}
    selectedTeacher={teacherName}
  />
{/if}

<Loader show={isLoadingData} message="Cargando datos del registro..." />

<SyncNotification
  show={notificationConfig.show}
  message={notificationConfig.message}
  type={notificationConfig.type}
/>

<!-- Drive Folder Picker -->
{#if showDrivePicker}
  <DriveFolderPicker
    onSelect={handleDriveFolderSelect}
    onClose={() => (showDrivePicker = false)}
  />
{/if}

<style>
  .custom-scrollbar::-webkit-scrollbar {
    width: 6px;
  }
  .custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
  }
  .custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(0, 0, 0, 0.1);
    border-radius: 10px;
  }
  .custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(0, 0, 0, 0.2);
  }

  select option {
    background: white;
    color: #1a1a1a;
  }
</style>
