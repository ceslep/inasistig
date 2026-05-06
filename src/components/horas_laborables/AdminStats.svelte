<script lang="ts">
    import { onMount } from "svelte";
    import { fade, fly } from "svelte/transition";
    import Swal from "sweetalert2";
    import { sheetsService } from "./services/google_sheets_service.svelte";
    import Loader from "./Loader.svelte";

    interface Props {
        onBack: () => void;
        selectedTeacher?: string;
    }

    let { onBack, selectedTeacher = "" }: Props = $props();
    let isLoading = $state(true);
    let rows: any[] = $state([]);
    let monthlyStats: Record<string, number> = $state({});
    let categoryStats: Record<string, number> = $state({});
    let totalRecords = $state(0);
    let topTeacher = $state({ name: "", count: 0 });
    let allTeachers: string[] = $state([]);
    let selectedTeacherForStats = $state("");
    let selectedMonthForStats: string = $state("");
    let calendarModalData: { teacher: string; month: string; data: Record<number, string> } | null = $state(null);

    const categories = [
        {
            id: "normal",
            label: "Trabajo Normal",
            color: "bg-blue-500",
            icon: "💼",
        },
        {
            id: "ordinario",
            label: "Permiso Ordinario",
            color: "bg-green-500",
            icon: "📝",
        },
        {
            id: "sindical",
            label: "Permiso Sindical",
            color: "bg-purple-500",
            icon: "⚖️",
        },
        {
            id: "jurado",
            label: "Jurado de Votación",
            color: "bg-emerald-600",
            icon: "🗳️",
        },
        {
            id: "gobernacion",
            label: "Día Libre Gobernación",
            color: "bg-cyan-600",
            icon: "🏛️",
        },
        {
            id: "profesor",
            label: "Día del Profesor",
            color: "bg-rose-500",
            icon: "🍎",
        },
        {
            id: "calamidad",
            label: "Calamidad Doméstica",
            color: "bg-stone-500",
            icon: "🏠",
        },
        {
            id: "luto",
            label: "Licencia por Luto",
            color: "bg-slate-600",
            icon: "✝️",
        },
        {
            id: "medica",
            label: "Incapacidad Médica",
            color: "bg-red-500",
            icon: "🏥",
        },
        {
            id: "maternidad",
            label: "Licencia Mat./Pat.",
            color: "bg-teal-500",
            icon: "👶",
        },
        {
            id: "secretaria",
            label: "Permisos/Capacitaciones Secretaría",
            color: "bg-amber-500",
            icon: "🎓",
        },
        {
            id: "bienestar",
            label: "Bienestar Docente",
            color: "bg-pink-500",
            icon: "🧘",
        },
        {
            id: "pedagogica",
            label: "Jornada Pedagógica",
            color: "bg-violet-500",
            icon: "📓",
        },
        {
            id: "familia",
            label: "Día de la Familia",
            color: "bg-orange-500",
            icon: "👨‍👩‍👧‍👦",
        },
        {
            id: "cumpleaños",
            label: "Cumpleaños",
            color: "bg-yellow-500",
            icon: "🎂",
        },
        {
            id: "quinquenio",
            label: "Quinquenio",
            color: "bg-indigo-600",
            icon: "🏅",
        },
        {
            id: "vacaciones",
            label: "Vacaciones",
            color: "bg-sky-500",
            icon: "✈️",
        },
        { id: "paro", label: "Paro / Huelga", color: "bg-red-700", icon: "🛑" },
        {
            id: "sabdomfest",
            label: "Sábados, Domingos y Festivos",
            color: "bg-slate-400",
            icon: "📅",
        },
    ];

    onMount(async () => {
        try {
            const result = await sheetsService.getRows();
            if (result.success && result.records) {
                rows = result.records.map(record => record.values); // Extract only the values
                // Initialize selected teacher if provided
                if (selectedTeacher) {
                    selectedTeacherForStats = selectedTeacher;
                }
                processStats(rows);
            }
        } catch (error) {
            console.error("Error fetching stats:", error);
        } finally {
            isLoading = false;
        }
    });

    // Recalculate stats when selected teacher changes
    $effect(() => {
        if (rows.length > 0) {
            processStats(rows);
        }
    });

    function normalizeString(str: string): string {
        return str.trim().toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
    }

    function processStats(data: any[]) {
        // Data structure: [Timestamp, Email, Docente, Mes, ...Days]
        // Usually row 0 is header. Assuming row[2] is teacher, row[3] is month.

        // Skip header if it contains "Docente" or "Nombre" or if Month is "MES"
        let cleanData = data.filter(
            (row) =>
                row[2] &&
                row[2] !== "Nombre Docente" &&
                row[3] &&
                row[3] !== "MES",
        );

        // Extract all unique teachers (normalize to handle accent variations)
        const teacherMap = new Map<string, string>();
        cleanData.forEach((row) => {
            if (row[2]) {
                const normalized = normalizeString(row[2]);
                if (!teacherMap.has(normalized)) {
                    teacherMap.set(normalized, row[2].trim());
                }
            }
        });
        allTeachers = Array.from(teacherMap.values()).sort((a, b) => a.localeCompare(b, 'es'));

        if (selectedTeacherForStats) {
            const normalizedSelected = normalizeString(selectedTeacherForStats);
            cleanData = cleanData.filter((row) => row[2] && normalizeString(row[2]) === normalizedSelected);
        }

        // Filter by selected month if any
        if (selectedMonthForStats) {
            cleanData = cleanData.filter((row) => row[3]?.trim() === selectedMonthForStats.trim());
        }

        totalRecords = cleanData.length;

        // Stats by Month - Count actual days registered, not just records
        const months: Record<string, number> = {};
        const monthOrder = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
        
        cleanData.forEach((row) => {
            const month = row[3];
            const days = row.slice(4); // Get days data (indexes 4-34)
            
            // Count non-empty days for this record
            let dayCount = 0;
            days.forEach((day: string) => {
                if (day && day.trim() !== "") {
                    dayCount++;
                }
            });
            
            months[month] = (months[month] || 0) + dayCount;
        });
        monthlyStats = months;

        // Stats by Teacher
        const teachers: Record<string, number> = {};
        cleanData.forEach((row) => {
            const teacher = row[2];
            teachers[teacher] = (teachers[teacher] || 0) + 1;
        });

        // Stats by Category (Iterate through days - index 4 to end)
        const cats: Record<string, number> = {};
        cleanData.forEach((row) => {
            for (let i = 4; i < row.length; i++) {
                const cellValue = row[i];
                if (cellValue) {
                    // Check if cellValue matches a category ID
                    // Ensure whitespace trimming
                    const id = cellValue.trim();
                    if (categories.some((c) => c.id === id)) {
                        cats[id] = (cats[id] || 0) + 1;
                    }
                }
            }
        });
        categoryStats = cats;

        // Find top teacher
        let max = 0;
        let maxTeacher = "N/A";
        Object.entries(teachers).forEach(([name, count]) => {
            if (count > max) {
                max = count;
                maxTeacher = name;
            }
        });
        topTeacher = { name: maxTeacher, count: max };
    }

    // Helper for max value in monthly stats for bar chart scaling
    function getMaxMonthCount() {
        const values = Object.values(monthlyStats);
        return values.length ? Math.max(...values) : 1;
    }

function selectMonthForStats(month: string) {
        selectedMonthForStats = selectedMonthForStats === month ? "" : month;
    }

    function showMonthCalendar(month: string) {
        const teacher = selectedTeacherForStats;
        const normalizedTeacher = normalizeString(teacher);
        
        // Find the record for this teacher and month
        const record = rows.find(row => {
            const rowTeacher = row[2]?.trim();
            const rowMonth = row[3]?.trim();
            return normalizeString(rowTeacher) === normalizedTeacher && rowMonth === month;
        });

        if (!record) {
            Swal.fire({
                title: `${teacher} - ${month}`,
                text: "No hay datos registrados para este mes",
                icon: "info"
            });
            return;
        }

        // Extract day data from record (indexes 4-34)
        const dayData: Record<number, string> = {};
        for (let i = 4; i <= 34; i++) {
            const dayValue = record[i];
            if (dayValue && dayValue.trim() !== "") {
                dayData[i - 3] = dayValue.trim();
            }
        }

        const calendarContent = generateCalendarHTML(month, dayData);
        
        Swal.fire({
            title: `${teacher} - ${month}`,
            html: calendarContent,
            width: '800px',
            showConfirmButton: false,
            showCloseButton: true,
        });
    }

    function generateCalendarHTML(monthName: string, dayData: Record<number, string>): string {
        const months = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
        const monthIndex = months.indexOf(monthName);
        const year = new Date().getFullYear();
        const daysInMonthCount = new Date(year, monthIndex + 1, 0).getDate();
        const startDay = (new Date(year, monthIndex, 1).getDay() + 6) % 7;

        const colorMap: Record<string, string> = {
            'normal': '#3b82f6',
            'ordinario': '#22c55e',
            'sindical': '#a855f7',
            'jurado': '#059669',
            'gobernacion': '#0891b2',
            'profesor': '#f43f5e',
            'calamidad': '#78716c',
            'luto': '#475569',
            'medica': '#ef4444',
            'maternidad': '#14b8a6',
            'secretaria': '#f59e0b',
            'bienestar': '#ec4899',
            'pedagogica': '#8b5cf6',
            'familia': '#f97316',
            'cumpleaños': '#eab308',
            'quinquenio': '#4f46e5',
            'vacaciones': '#0ea5e9',
            'paro': '#b91c1c',
            'sabdomfest': '#94a3b8',
        };

        const labelMap: Record<string, string> = {
            'normal': 'Normal',
            'ordinario': 'Ordinario',
            'sindical': 'Sindical',
            'jurado': 'Jurado',
            'gobernacion': 'Gobernación',
            'profesor': 'Profesor',
            'calamidad': 'Calamidad',
            'luto': 'Luto',
            'medica': 'Médica',
            'maternidad': 'Licencia',
            'secretaria': 'Secretaría',
            'bienestar': 'Bienestar',
            'pedagogica': 'Pedagógica',
            'familia': 'Familia',
            'cumpleaños': 'Cumpleaños',
            'quinquenio': 'Quinquenio',
            'vacaciones': 'Vacaciones',
            'paro': 'Paro',
            'sabdomfest': 'SDF',
        };

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

        for (let i = 0; i < startDay; i++) {
            calendarHTML += `<div></div>`;
        }

        for (let day = 1; day <= daysInMonthCount; day++) {
            const categoryId = dayData[day];
            const bgColor = categoryId ? (colorMap[categoryId] || '#94a3b8') : null;
            const label = categoryId ? (labelMap[categoryId] || categoryId) : null;

            if (bgColor) {
                calendarHTML += `
                    <div class="aspect-square rounded-lg flex flex-col items-center justify-center text-xs font-medium text-white shadow-md"
                        style="background-color: ${bgColor};" title="${label}">
                        <span>${day}</span>
                        <span class="text-[8px] opacity-90 hidden sm:block">${label}</span>
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


</script>

<div
    class="fixed inset-0 z-50 bg-slate-50 overflow-y-auto"
    in:fade={{ duration: 300 }}
>
    {#if isLoading}
        <Loader show={true} message="Analizando datos..." />
    {:else}
        <div class="max-w-6xl mx-auto p-4 md:p-8 space-y-8">
            <!-- Header -->
            <div
                class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4"
            >
                <div class="w-full">
                    <h1 class="text-3xl font-bold text-slate-900">
                        {selectedTeacherForStats
                            ? "Estadísticas Docentes"
                            : "Panel Global"}
                    </h1>
                    <p class="text-slate-500 mb-4">
                        {selectedTeacherForStats
                            ? `Resumen detallado para: ${selectedTeacherForStats}`
                            : "Resumen acumulado de todos los registros"}
                    </p>
                    
                    <!-- Teacher Selector -->
                    <div class="max-w-md">
                        <label for="teacher-select" class="text-xs font-bold text-slate-600 uppercase tracking-widest block mb-2">
                            Seleccionar Docente
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <select
                                id="teacher-select"
                                bind:value={selectedTeacherForStats}
                                class="w-full bg-white border border-slate-300 rounded-xl pl-11 pr-10 py-3 text-slate-900 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all appearance-none cursor-pointer shadow-sm hover:border-slate-400"
                            >
                                <option value="">Todos los Docentes</option>
                                {#each allTeachers as teacher}
                                    <option value={teacher}>{teacher}</option>
                                {/each}
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
                <button
                    onclick={onBack}
                    class="px-6 py-2.5 bg-white border border-slate-200 text-slate-700 font-semibold rounded-xl hover:bg-slate-50 transition-colors shadow-sm"
                >
                    Volver al Registro
                </button>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Card 1 -->
                <div
                    class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 relative overflow-hidden"
                >
                    <div class="absolute top-0 right-0 p-4 opacity-10">
                        <span class="text-6xl">📊</span>
                    </div>
                    <p
                        class="text-xs font-bold text-slate-400 uppercase tracking-widest"
                    >
                        {selectedMonthForStats ? "Total Actividades" : "Total Registros"}
                    </p>
                    <p class="text-4xl font-bold text-slate-900 mt-2">
                        {selectedMonthForStats ? Object.values(categoryStats).reduce((sum, count) => sum + count, 0) : totalRecords}
                    </p>
                </div>

                <!-- Card 2 -->
                <div
                    class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 relative overflow-hidden"
                >
                    <div class="absolute top-0 right-0 p-4 opacity-10">
                        <span class="text-6xl">🏆</span>
                    </div>
                    <p
                        class="text-xs font-bold text-slate-400 uppercase tracking-widest"
                    >
                        Docente Más Activo
                    </p>
                    <p
                        class="text-lg font-bold text-slate-900 mt-2 line-clamp-1"
                        title={topTeacher.name}
                    >
                        {topTeacher.name}
                    </p>
                    <p class="text-sm text-slate-500">
                        {topTeacher.count} registros
                    </p>
                </div>
                <!-- Card 3 -->
                <div
                    class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 relative overflow-hidden"
                >
                    <div class="absolute top-0 right-0 p-4 opacity-10">
                        <span class="text-6xl">📅</span>
                    </div>
                    <p
                        class="text-xs font-bold text-slate-400 uppercase tracking-widest"
                    >
                        Meses Registrados
                    </p>
                    <p class="text-4xl font-bold text-slate-900 mt-2">
                        {Object.keys(monthlyStats).length}
                    </p>
                </div>
            </div>

            <!-- Category Stats Breakdown -->
            <div
                class="bg-white rounded-3xl shadow-lg border border-slate-100 overflow-hidden"
            >
                <div class="p-6 border-b border-slate-50">
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="text-xl font-bold text-slate-900">
                                {selectedMonthForStats ? `Desglose por Tipo de Actividad - ${selectedMonthForStats}` : 'Desglose por Tipo de Actividad'}
                            </h2>
                            <p class="text-sm text-slate-500 mt-1">
                                {selectedMonthForStats 
                                    ? `Conteo de días para ${selectedMonthForStats} ${selectedTeacherForStats ? `- ${selectedTeacherForStats}` : '- Todos los docentes'}` 
                                    : `Conteo total de días por cada tipo de novedad ${selectedTeacherForStats ? `- ${selectedTeacherForStats}` : '- Todos los docentes'}`}
                            </p>
                        </div>
                        {#if selectedMonthForStats}
                            <button
                                type="button"
                                onclick={() => selectedMonthForStats = ""}
                                class="text-sm text-blue-600 hover:text-blue-700 font-medium flex items-center gap-1"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Limpiar filtro
                            </button>
                        {/if}
                    </div>
                </div>
                <div class="p-6">
                    <div
                        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4"
                    >
                        {#each categories as cat}
                            {#if categoryStats[cat.id]}
                                <div
                                    class="flex items-center gap-3 p-3 rounded-xl border border-slate-100 bg-slate-50/50"
                                >
                                    <div
                                        class="w-10 h-10 rounded-full flex items-center justify-center text-lg shadow-sm {cat.color.replace(
                                            'bg-',
                                            'bg-opacity-20 text-',
                                        )}"
                                    >
                                        <span class="drop-shadow-sm"
                                            >{cat.icon}</span
                                        >
                                    </div>
                                    <div>
                                        <p
                                            class="text-xs font-bold text-slate-500 uppercase"
                                        >
                                            {cat.label}
                                        </p>
                                        <p
                                            class="text-xl font-bold text-slate-900"
                                        >
                                            {categoryStats[cat.id]}
                                        </p>
                                    </div>
                                </div>
                            {/if}
                        {/each}
                    </div>
                </div>
            </div>

            <!-- Monthly Stats -->
            <div
                class="bg-white rounded-3xl shadow-lg border border-slate-100 overflow-hidden"
            >
                <div class="p-6 border-b border-slate-50">
                    <h2 class="text-xl font-bold text-slate-900">
                        Registros por Mes
                    </h2>
                </div>
                <div class="p-6 space-y-4">
                     {#each Object.entries(monthlyStats).sort((a, b) => {
                         const monthOrder = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
                         const aIndex = monthOrder.indexOf(a[0]);
                         const bIndex = monthOrder.indexOf(b[0]);
                         return aIndex - bIndex;
                     }) as [month, count], i}
                        <div class="space-y-2">
                            <button
                                type="button"
                                class="w-full flex justify-between text-sm font-medium cursor-pointer hover:bg-slate-50 p-2 rounded-lg transition-colors group text-left {selectedMonthForStats === month ? 'bg-blue-50 border border-blue-200' : ''}"
                                onclick={() => showMonthCalendar(month)}
                            >
                                <div class="flex items-center gap-2">
                                    <span class="text-slate-700 group-hover:text-blue-600">{month}</span>
                                    <svg class="w-4 h-4 text-slate-400 group-hover:text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </div>
                                <span class="text-slate-900 font-bold group-hover:text-blue-600">{count} días</span>
                            </button>
                            <!-- Progress Bar -->
                            <div
                                class="h-3 w-full bg-slate-100 rounded-full overflow-hidden"
                            >
                                <div
                                    class="h-full bg-blue-500 rounded-full"
                                    style="width: {(count /
                                        getMaxMonthCount()) *
                                        100}%"
                                    in:fly={{
                                        x: -20,
                                        duration: 500,
                                        delay: i * 50,
                                    }}
                                ></div>
                            </div>
                        </div>
                    {/each}
                    {#if Object.keys(monthlyStats).length === 0}
                        <div class="text-center py-10 text-slate-400">
                            No hay datos suficientes para mostrar estadísticas.
                        </div>
                    {/if}
                </div>
            </div>
        </div>
    {/if}
</div>
