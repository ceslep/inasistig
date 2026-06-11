<script lang="ts">
  import { onMount } from 'svelte'
  import Swal from 'sweetalert2'
  import { Loader2, Download, FileText, FileSpreadsheet, X, Filter, Calendar, User, BookOpen, Clock, ChevronDown, Check, Printer, Settings2, Users } from '@lucide/svelte'
  import { horasExtrasSheetsService } from './services/google_sheets_service.svelte'
  import { getDocentes, getMaterias, getOpcionesAnotador } from '../../../api/service'
  import ModuleHeader from '../ModuleHeader.svelte'
  import SortableItem from './SortableItem.svelte'

  let { onBack }: { onBack: () => void } = $props()

  interface RegistroHorasExtras {
    fecha: string
    dia: string
    mes: string
    año: string
    docente: string
    horaEntrada: string
    horaSalida: string
    gradoAtendido: string
    asignatura: string
    actividad: string
    horasExtras: number
    observaciones: string
    escalafon: string
    cedula: string
    rowIndex: number
  }

  type CampoReporte = {
    id: string
    label: string
    enabled: boolean
    order: number
  }

  type FiltroReporte = {
    docente: string[]
    fechaInicio: string
    fechaFin: string
    grado: string
    asignatura: string
  }

  const CAMPOS_DISPONIBLES: CampoReporte[] = [
    { id: 'cedula', label: 'Cédula', enabled: true, order: 0 },
    { id: 'docente', label: 'Docente', enabled: true, order: 1 },
    { id: 'fecha', label: 'Fecha', enabled: true, order: 2 },
    { id: 'asignatura', label: 'Asignatura', enabled: true, order: 3 },
    { id: 'grado', label: 'Grado', enabled: true, order: 4 },
    { id: 'escalafon', label: 'Escalafón', enabled: true, order: 5 },
    { id: 'actividad', label: 'Actividad', enabled: true, order: 6 },
    { id: 'horaEntrada', label: 'Hora Entrada', enabled: false, order: 7 },
    { id: 'horaSalida', label: 'Hora Salida', enabled: false, order: 8 },
    { id: 'observaciones', label: 'Observaciones', enabled: false, order: 9 },
  ]

  let docentesList = $state<string[]>([])
  let materiasList = $state<{ materia: string }[]>([])
  let actividadesList = $state<string[]>([])
  let gradosDisponibles = $state<string[]>([])

  let isLoadingData = $state(true)
  let isLoadingRecords = $state(false)
  let isGeneratingPdf = $state(false)
  let isGeneratingExcel = $state(false)
  let showColumnConfig = $state(false)
  let showDocenteSelector = $state(false)
  let docenteSearchTerm = $state('')
  let savedDocenteGroups = $state<{ name: string; docentes: string[] }[]>([])

  let allRecords = $state<RegistroHorasExtras[]>([])

  const MESES = [
    { value: '01', label: 'Enero' },
    { value: '02', label: 'Febrero' },
    { value: '03', label: 'Marzo' },
    { value: '04', label: 'Abril' },
    { value: '05', label: 'Mayo' },
    { value: '06', label: 'Junio' },
    { value: '07', label: 'Julio' },
    { value: '08', label: 'Agosto' },
    { value: '09', label: 'Septiembre' },
    { value: '10', label: 'Octubre' },
    { value: '11', label: 'Noviembre' },
    { value: '12', label: 'Diciembre' },
  ]

  let mesSeleccionado = $state(getCurrentMes())
  let anioSeleccionado = $state(new Date().getFullYear())

  function getInitialFechaFin(): string {
    const now = new Date()
    const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0).getDate()
    return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(lastDay).padStart(2, '0')}`
  }

  let filtros = $state<FiltroReporte>({
    docente: [],
    fechaInicio: getFirstDayOfMonth(),
    fechaFin: getInitialFechaFin(),
    grado: '',
    asignatura: ''
  })

  let camposReporte = $state<CampoReporte[]>(
    CAMPOS_DISPONIBLES.map(c => ({ ...c }))
  )

  let registrosFiltrados = $state<RegistroHorasExtras[]>([])

  function getFirstDayOfMonth(): string {
    const now = new Date()
    return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-01`
  }

  function getTodayDate(): string {
    const now = new Date()
    return now.toISOString().split('T')[0]
  }

  function getCurrentMes(): string {
    const now = new Date()
    return String(now.getMonth() + 1).padStart(2, '0')
  }

  function getLastDayOfMonth(year: number, month: number): string {
    const lastDay = new Date(year, month, 0).getDate()
    return `${year}-${String(month).padStart(2, '0')}-${String(lastDay).padStart(2, '0')}`
  }

  function onMesChange() {
    const mes = parseInt(mesSeleccionado)
    const anio = anioSeleccionado
    filtros.fechaInicio = `${anio}-${String(mes).padStart(2, '0')}-01`
    filtros.fechaFin = getLastDayOfMonth(anio, mes)
    aplicarFiltros()
  }

  function onAnioChange() {
    const mes = parseInt(mesSeleccionado)
    const anio = anioSeleccionado
    filtros.fechaInicio = `${anio}-${String(mes).padStart(2, '0')}-01`
    filtros.fechaFin = getLastDayOfMonth(anio, mes)
    aplicarFiltros()
  }

  function normalizeAccents(text: string): string {
    return text.toLowerCase().trim().normalize("NFD").replace(/[\u0300-\u036f]/g, "")
  }

  function parsearRegistro(values: string[], rowIndex: number): RegistroHorasExtras {
    return {
      fecha: values[0] || '',
      dia: values[1] || '',
      mes: values[2] || '',
      año: values[3] || '',
      docente: values[4] || '',
      horaEntrada: values[5] || '',
      horaSalida: values[6] || '',
      gradoAtendido: values[7] || '',
      asignatura: values[8] || '',
      actividad: values[9] || '',
      horasExtras: parseFloat(values[10]) || 0,
      observaciones: values[12] || '',
      escalafon: values[14] || '',
      cedula: values[15] || '',
      rowIndex
    }
  }

  async function loadInitialData() {
    isLoadingData = true
    try {
      const [docentesData, materiasData, opcionesData] = await Promise.all([
        getDocentes(),
        getMaterias(),
        getOpcionesAnotador()
      ])

      const teacherMap = new Map<string, string>()
      ;(docentesData as string[]).forEach((teacher: string) => {
        const normalized = teacher.trim().toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "")
        if (!teacherMap.has(normalized)) {
          teacherMap.set(normalized, teacher.trim())
        }
      })
      docentesList = Array.from(teacherMap.values()).sort((a, b) => a.localeCompare(b, 'es'))

      materiasList = materiasData || []

      const allActividades: string[] = []
      if (opcionesData && typeof opcionesData === 'object') {
        Object.values(opcionesData as Record<string, string[]>).forEach((items) => {
          allActividades.push(...items)
        })
      }
      actividadesList = [...new Set(allActividades)].sort()

      await loadAllRecords()
    } catch (error) {
      console.error('Error cargando datos:', error)
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'No se pudieron cargar los datos iniciales',
        confirmButtonColor: '#ef4444'
      })
    } finally {
      isLoadingData = false
    }
  }

  async function loadAllRecords() {
    isLoadingRecords = true
    try {
      const result = await horasExtrasSheetsService.getAllRegistros()
      if (result.success && result.records) {
        allRecords = result.records
          .filter(r => r.values && r.values.length >= 14)
          .map(r => parsearRegistro(r.values, r.rowIndex))
        actualizarGradosDisponibles()
        aplicarFiltros()
      }
    } catch (error) {
      console.error('Error cargando registros:', error)
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'No se pudieron cargar los registros de horas extras',
        confirmButtonColor: '#ef4444'
      })
    } finally {
      isLoadingRecords = false
    }
  }

  function actualizarGradosDisponibles() {
    const gradosSet = new Set<string>()
    allRecords.forEach(r => {
      if (r.gradoAtendido) {
        gradosSet.add(r.gradoAtendido)
      }
    })
    gradosDisponibles = [...gradosSet].sort((a, b) => {
      const numA = parseInt(a.replace(/[^0-9]/g, '')) || 0
      const numB = parseInt(b.replace(/[^0-9]/g, '')) || 0
      return numA - numB
    })
  }

  function aplicarFiltros() {
    let filtered = [...allRecords]

    if (filtros.docente.length > 0) {
      const docentesNorm = filtros.docente.map(d => normalizeAccents(d))
      filtered = filtered.filter(r => docentesNorm.includes(normalizeAccents(r.docente)))
    }

    if (filtros.fechaInicio) {
      filtered = filtered.filter(r => r.fecha >= filtros.fechaInicio)
    }

    if (filtros.fechaFin) {
      filtered = filtered.filter(r => r.fecha <= filtros.fechaFin)
    }

    if (filtros.grado) {
      filtered = filtered.filter(r => r.gradoAtendido === filtros.grado)
    }

    if (filtros.asignatura) {
      const asigNorm = normalizeAccents(filtros.asignatura)
      filtered = filtered.filter(r => normalizeAccents(r.asignatura) === asigNorm)
    }

    filtered.sort((a, b) => b.fecha.localeCompare(a.fecha))

    registrosFiltrados = filtered
  }

  function getEnabledCampos(): CampoReporte[] {
    return camposReporte
      .filter(c => c.enabled)
      .sort((a, b) => a.order - b.order)
  }

  function getDiasDelMes(): number[] {
    if (!filtros.fechaInicio) return []
    const fecha = new Date(filtros.fechaInicio + 'T00:00:00')
    const daysInMonth = new Date(fecha.getFullYear(), fecha.getMonth() + 1, 0).getDate()
    return Array.from({ length: daysInMonth }, (_, i) => i + 1)
  }

  function getDocentesUnicos(): string[] {
    const docentesSet = new Set<string>()
    registrosFiltrados.forEach(r => {
      if (r.docente) docentesSet.add(r.docente)
    })
    return [...docentesSet].sort((a, b) => a.localeCompare(b, 'es'))
  }

  function getRegistrosPorDocente(docente: string): RegistroHorasExtras[] {
    return registrosFiltrados.filter(r => normalizeAccents(r.docente) === normalizeAccents(docente))
  }

  function getFilasUnicasPorDocente(docente: string): { actividad: string; asignatura: string; grado: string }[] {
    const registros = getRegistrosPorDocente(docente)
    const filasSet = new Set<string>()
    const filas: { actividad: string; asignatura: string; grado: string }[] = []

    registros.forEach(r => {
      const key = `${r.actividad}|${r.asignatura}|${r.gradoAtendido}`
      if (!filasSet.has(key)) {
        filasSet.add(key)
        filas.push({
          actividad: r.actividad,
          asignatura: r.asignatura,
          grado: r.gradoAtendido
        })
      }
    })

    return filas.sort((a, b) => {
      const comp = a.asignatura.localeCompare(b.asignatura, 'es')
      if (comp !== 0) return comp
      return a.actividad.localeCompare(b.actividad, 'es')
    })
  }

  function getHorasParaFilaDia(docente: string, actividad: string, asignatura: string, grado: string, dia: number): number {
    const registros = getRegistrosPorDocente(docente)
    const diaStr = dia.toString().padStart(2, '0')

    return registros
      .filter(r => {
        const matchActividad = normalizeAccents(r.actividad) === normalizeAccents(actividad)
        const matchAsignatura = normalizeAccents(r.asignatura) === normalizeAccents(asignatura)
        const matchGrado = normalizeAccents(r.gradoAtendido) === normalizeAccents(grado)
        const matchDia = r.dia === diaStr
        return matchActividad && matchAsignatura && matchGrado && matchDia
      })
      .reduce((sum, r) => sum + r.horasExtras, 0)
  }

  function getTotalHoras(): number {
    return registrosFiltrados.reduce((sum, r) => sum + r.horasExtras, 0)
  }

  function toggleCampo(campoId: string) {
    camposReporte = camposReporte.map(c =>
      c.id === campoId ? { ...c, enabled: !c.enabled } : c
    )
  }

  function moveCampoUp(campoId: string) {
    const enabledCampos = getEnabledCampos()
    const currentIndex = enabledCampos.findIndex(c => c.id === campoId)
    if (currentIndex <= 0) return

    const currentCampo = enabledCampos[currentIndex]
    const prevCampo = enabledCampos[currentIndex - 1]

    camposReporte = camposReporte.map(c => {
      if (c.id === campoId) return { ...c, order: prevCampo.order }
      if (c.id === prevCampo.id) return { ...c, order: currentCampo.order }
      return c
    })
  }

  function moveCampoDown(campoId: string) {
    const enabledCampos = getEnabledCampos()
    const currentIndex = enabledCampos.findIndex(c => c.id === campoId)
    if (currentIndex === -1 || currentIndex >= enabledCampos.length - 1) return

    const currentCampo = enabledCampos[currentIndex]
    const nextCampo = enabledCampos[currentIndex + 1]

    camposReporte = camposReporte.map(c => {
      if (c.id === campoId) return { ...c, order: nextCampo.order }
      if (c.id === nextCampo.id) return { ...c, order: currentCampo.order }
      return c
    })
  }

  async function generarReportePDF() {
    if (registrosFiltrados.length === 0) {
      await Swal.fire({
        icon: 'warning',
        title: 'Sin datos',
        text: 'No hay datos para generar el reporte',
        confirmButtonColor: '#f59e0b'
      })
      return
    }

    isGeneratingPdf = true

    try {
      const { jsPDF, autoTable } = await loadPdfLibraries()
      const doc = new jsPDF()

      let titulo = 'Reporte de Horas Extras'
      if (filtros.docente.length > 0) {
        titulo += ` - ${filtros.docente.join(', ')}`
      }

      doc.setFontSize(16)
      doc.setTextColor(40, 40, 40)
      doc.text(titulo, 14, 20)

      doc.setFontSize(10)
      doc.setTextColor(100, 100, 100)
      const filtrosActivos: string[] = []
      if (filtros.docente.length > 0) filtrosActivos.push(`Docentes: ${filtros.docente.join(', ')}`)
      if (filtros.fechaInicio && filtros.fechaFin) {
        filtrosActivos.push(`Fecha: ${filtros.fechaInicio} - ${filtros.fechaFin}`)
      }
      if (filtros.grado) filtrosActivos.push(`Grado: ${filtros.grado}`)
      if (filtros.asignatura) filtrosActivos.push(`Asignatura: ${filtros.asignatura}`)
      doc.text('Filtros: ' + filtrosActivos.join(', '), 14, 28)

      const enabledCampos = getEnabledCampos()
      const headRow = enabledCampos.map(c => c.label).concat(['Total'])

      const tableData = registrosFiltrados.map(r => {
        const row: string[] = []
        enabledCampos.forEach(c => {
          switch (c.id) {
            case 'cedula': row.push(r.cedula); break
            case 'docente': row.push(r.docente); break
            case 'fecha': row.push(r.fecha); break
            case 'asignatura': row.push(r.asignatura); break
            case 'grado': row.push(r.gradoAtendido); break
            case 'escalafon': row.push(r.escalafon); break
            case 'actividad': row.push(r.actividad); break
            case 'horaEntrada': row.push(r.horaEntrada); break
            case 'horaSalida': row.push(r.horaSalida); break
            case 'observaciones': row.push(r.observaciones || '-'); break
            default: row.push('')
          }
        })
        row.push(r.horasExtras.toFixed(2))
        return row
      })

      autoTable(doc, {
        head: [headRow],
        body: tableData,
        startY: 35,
        styles: { fontSize: 8, cellPadding: 2, overflow: 'linebreak' },
        headStyles: { fillColor: [46, 117, 182], textColor: 255, fontStyle: 'bold' },
        alternateRowStyles: { fillColor: [245, 247, 250] },
        margin: { left: 14, right: 14 }
      })

      const finalY = (doc as unknown as { lastAutoTable?: { finalY: number } }).lastAutoTable?.finalY || 100

      doc.setFontSize(12)
      doc.setTextColor(34, 197, 94)
      doc.text(`Total Horas: ${getTotalHoras().toFixed(2)}`, 14, finalY + 15)

      const fechaActual = new Date().toISOString().split('T')[0]
      doc.setFontSize(8)
      doc.setTextColor(150, 150, 150)
      doc.text(`Generado: ${fechaActual}`, 14, doc.internal.pageSize.height - 10)

      doc.save(`reporte_horas_extras_${fechaActual}.pdf`)
    } catch (error) {
      console.error('Error generando PDF:', error)
      await Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'No se pudo generar el reporte PDF',
        confirmButtonColor: '#ef4444'
      })
    } finally {
      isGeneratingPdf = false
    }
  }

  async function generarReporteExcelPorDocente() {
    if (registrosFiltrados.length === 0) {
      await Swal.fire({
        icon: 'warning',
        title: 'Sin datos',
        text: 'No hay datos para generar el reporte',
        confirmButtonColor: '#f59e0b'
      })
      return
    }

    isGeneratingExcel = true

    try {
      const { ExcelJS } = await loadExcelLibraries()
      const workbook = new ExcelJS.Workbook()
      workbook.removeWorksheet('Sheet')

      const enabledCampos = getEnabledCampos()
      const diasDelMes = getDiasDelMes()

      const docentesUnicos = getDocentesUnicos()

      for (const docente of docentesUnicos) {
        const worksheet = workbook.addWorksheet(docente.substring(0, 31))

        const headerRow: (string | number)[] = []
        enabledCampos.forEach(c => {
          headerRow.push(c.label)
        })
        diasDelMes.forEach(dia => {
          headerRow.push(dia)
        })
        headerRow.push('Total')

        const headerExcelRow = worksheet.getRow(1)
        headerExcelRow.font = { bold: true }
        headerExcelRow.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: '2E75B6' } }
        headerExcelRow.font = { color: { argb: 'FFFFFFFF' }, bold: true }
        headerExcelRow.alignment = { horizontal: 'center' }

        headerRow.forEach((header, idx) => {
          const cell = headerExcelRow.getCell(idx + 1)
          cell.value = header
          if (idx >= enabledCampos.length) {
            cell.alignment = { horizontal: 'center' }
          }
        })

        const filasUnicas = getFilasUnicasPorDocente(docente)

        filasUnicas.forEach((fila, rowIdx) => {
          const excelRow = worksheet.addRow([])
          let totalDocente = 0

          enabledCampos.forEach(c => {
            switch (c.id) {
              case 'cedula': excelRow.getCell(enabledCampos.indexOf(c) + 1).value = getCedulaDocente(docente); break
              case 'docente': excelRow.getCell(enabledCampos.indexOf(c) + 1).value = docente; break
              case 'fecha': excelRow.getCell(enabledCampos.indexOf(c) + 1).value = `${filtros.fechaInicio} al ${filtros.fechaFin}`; break
              case 'asignatura': excelRow.getCell(enabledCampos.indexOf(c) + 1).value = fila.asignatura; break
              case 'grado': excelRow.getCell(enabledCampos.indexOf(c) + 1).value = fila.grado; break
              case 'escalafon': excelRow.getCell(enabledCampos.indexOf(c) + 1).value = getEscalafonDocente(docente); break
              case 'actividad': excelRow.getCell(enabledCampos.indexOf(c) + 1).value = fila.actividad; break
              case 'horaEntrada': excelRow.getCell(enabledCampos.indexOf(c) + 1).value = ''; break
              case 'horaSalida': excelRow.getCell(enabledCampos.indexOf(c) + 1).value = ''; break
              case 'observaciones': excelRow.getCell(enabledCampos.indexOf(c) + 1).value = ''; break
            }
          })

          diasDelMes.forEach((dia, diaIdx) => {
            const horas = getHorasParaFilaDia(docente, fila.actividad, fila.asignatura, fila.grado, dia)
            const cell = excelRow.getCell(enabledCampos.length + diaIdx + 1)
            if (horas > 0) {
              cell.value = horas
              totalDocente += horas
            }
          })

          const totalCell = excelRow.getCell(enabledCampos.length + diasDelMes.length + 1)
          totalCell.value = totalDocente
          totalCell.font = { bold: true }
          totalCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'E8F5E9' } }

          if (rowIdx % 2 === 1) {
            excelRow.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'F5F7FA' } }
          }
        })

        const colCount = enabledCampos.length + diasDelMes.length + 1
        for (let i = 0; i < colCount; i++) {
          const col = worksheet.getColumn(i + 1)
          if (i < enabledCampos.length) {
            col.width = 15
          } else {
            col.width = 5
          }
        }
      }

      const fechaActual = new Date().toISOString().split('T')[0]
      const blob = await workbook.xlsx.writeBuffer()
      const link = document.createElement('a')
      link.href = URL.createObjectURL(new Blob([blob], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' }))
      link.download = `reporte_horas_extras_por_docente_${fechaActual}.xlsx`
      link.click()
      URL.revokeObjectURL(link.href)
    } catch (error) {
      console.error('Error generando Excel:', error)
      await Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'No se pudo generar el reporte Excel',
        confirmButtonColor: '#ef4444'
      })
    } finally {
      isGeneratingExcel = false
    }
  }

  function getCedulaDocente(docente: string): string {
    const reg = registrosFiltrados.find(r => normalizeAccents(r.docente) === normalizeAccents(docente))
    return reg?.cedula || ''
  }

  function getEscalafonDocente(docente: string): string {
    const reg = registrosFiltrados.find(r => normalizeAccents(r.docente) === normalizeAccents(docente))
    return reg?.escalafon || ''
  }

  async function generarReporteExcel() {
    if (registrosFiltrados.length === 0) {
      await Swal.fire({
        icon: 'warning',
        title: 'Sin datos',
        text: 'No hay datos para generar el reporte',
        confirmButtonColor: '#f59e0b'
      })
      return
    }

    isGeneratingExcel = true

    try {
      const { ExcelJS } = await loadExcelLibraries()
      const workbook = new ExcelJS.Workbook()
      const worksheet = workbook.addWorksheet('Horas Extras')

      const enabledCampos = getEnabledCampos()

      worksheet.columns = enabledCampos.map(c => ({
        header: c.label,
        key: c.id,
        width: 15
      }))

      const headerRow = worksheet.getRow(1)
      headerRow.font = { bold: true }
      headerRow.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: '2E75B6' } }
      headerRow.font = { color: { argb: 'FFFFFFFF' }, bold: true }
      headerRow.alignment = { horizontal: 'center' }

      registrosFiltrados.forEach((r, i) => {
        const row = worksheet.addRow([])
        enabledCampos.forEach(c => {
          switch (c.id) {
            case 'cedula': row.getCell(enabledCampos.indexOf(c) + 1).value = r.cedula; break
            case 'docente': row.getCell(enabledCampos.indexOf(c) + 1).value = r.docente; break
            case 'fecha': row.getCell(enabledCampos.indexOf(c) + 1).value = r.fecha; break
            case 'asignatura': row.getCell(enabledCampos.indexOf(c) + 1).value = r.asignatura; break
            case 'grado': row.getCell(enabledCampos.indexOf(c) + 1).value = r.gradoAtendido; break
            case 'escalafon': row.getCell(enabledCampos.indexOf(c) + 1).value = r.escalafon; break
            case 'actividad': row.getCell(enabledCampos.indexOf(c) + 1).value = r.actividad; break
            case 'horaEntrada': row.getCell(enabledCampos.indexOf(c) + 1).value = r.horaEntrada; break
            case 'horaSalida': row.getCell(enabledCampos.indexOf(c) + 1).value = r.horaSalida; break
            case 'observaciones': row.getCell(enabledCampos.indexOf(c) + 1).value = r.observaciones || '-'; break
          }
        })
        if (i % 2 === 1) {
          row.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'F5F7FA' } }
        }
      })

      const totalRow = worksheet.addRow([])
      enabledCampos.forEach((c, i) => {
        totalRow.getCell(i + 1).value = ''
      })
      totalRow.getCell(1).value = 'TOTAL'
      totalRow.font = { bold: true }
      totalRow.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: '22C55E' } }
      totalRow.font = { color: { argb: 'FFFFFFFF' } }
      totalRow.getCell(enabledCampos.length).value = getTotalHoras()

      const fechaActual = new Date().toISOString().split('T')[0]
      const blob = await workbook.xlsx.writeBuffer()
      const link = document.createElement('a')
      link.href = URL.createObjectURL(new Blob([blob], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' }))
      link.download = `reporte_horas_extras_${fechaActual}.xlsx`
      link.click()
      URL.revokeObjectURL(link.href)
    } catch (error) {
      console.error('Error generando Excel:', error)
      await Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'No se pudo generar el reporte Excel',
        confirmButtonColor: '#ef4444'
      })
    } finally {
      isGeneratingExcel = false
    }
  }

  function loadPdfLibraries() {
    return import('jspdf').then(m => ({ jsPDF: m.jsPDF })).then(async (mod) => {
      const autoTable = (await import('jspdf-autotable')).default
      return { ...mod, autoTable }
    })
  }

  function loadExcelLibraries() {
    return import('exceljs').then(m => ({ ExcelJS: m.default }))
  }

  function limpiarFiltros() {
    filtros = {
      docente: [],
      fechaInicio: getFirstDayOfMonth(),
      fechaFin: getTodayDate(),
      grado: '',
      asignatura: ''
    }
    aplicarFiltros()
  }

  function loadSavedGroups() {
    try {
      const saved = localStorage.getItem('docenteGroups')
      if (saved) {
        savedDocenteGroups = JSON.parse(saved)
      }
    } catch {
      savedDocenteGroups = []
    }
  }

  function saveGroups() {
    localStorage.setItem('docenteGroups', JSON.stringify(savedDocenteGroups))
  }

  function saveCurrentAsGroup(name: string) {
    if (filtros.docente.length === 0) return
    savedDocenteGroups = [...savedDocenteGroups, { name, docentes: [...filtros.docente] }]
    saveGroups()
  }

  function loadGroup(group: { name: string; docentes: string[] }) {
    filtros.docente = [...group.docentes]
    docenteSearchTerm = ''
    showDocenteSelector = false
    aplicarFiltros()
  }

  function deleteGroup(name: string) {
    savedDocenteGroups = savedDocenteGroups.filter(g => g.name !== name)
    saveGroups()
  }

  let filteredDocentes = $derived(
    docenteSearchTerm
      ? docentesList.filter(d => d.toLowerCase().includes(docenteSearchTerm.toLowerCase()))
      : docentesList
  )

  onMount(() => {
    loadInitialData()
    loadSavedGroups()
  })

  $effect(() => {
    aplicarFiltros()
  })
</script>

<div class="min-h-screen bg-[rgb(var(--bg-primary))]">
  <ModuleHeader title="Reporte de Horas Extras" subtitle="Consulta y exportación de registros" onBack={onBack}>

    {#snippet actions()}
      <button
        onclick={() => showColumnConfig = !showColumnConfig}
        class="flex items-center gap-2 px-4 py-2 rounded-xl bg-[rgb(var(--card-bg))] border border-[rgb(var(--border-primary))] text-[rgb(var(--text-primary))] font-medium hover:bg-[rgb(var(--bg-secondary))] transition-colors"
      >
        <Settings2 class="w-4 h-4" />
        Columnas
      </button>
    {/snippet}

  </ModuleHeader>

  <div class="max-w-7xl mx-auto p-4 space-y-6">

    {#if showColumnConfig}
      <div class="bg-[rgb(var(--card-bg))] border border-[rgb(var(--card-border))] rounded-2xl p-6 shadow-lg">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-bold text-[rgb(var(--text-primary))] flex items-center gap-2">
            <Settings2 class="w-5 h-5 text-[rgb(var(--accent-primary))]" />
            Configuración de Columnas
          </h2>
          <button
            onclick={() => showColumnConfig = false}
            class="p-1 rounded hover:bg-[rgb(var(--bg-secondary))]"
          >
            <X class="w-5 h-5 text-[rgb(var(--text-muted))]" />
          </button>
        </div>

        <p class="text-sm text-[rgb(var(--text-muted))] mb-4">
          Seleccione las columnas a incluir y use las flechas para reordenar. Los campos seleccionados aparecerán en el orden indicado.
        </p>

        <div class="space-y-1">
          {#each getEnabledCampos() as campo, index (campo.id)}
            <SortableItem
              {campo}
              {index}
              totalEnabled={getEnabledCampos().length}
              onToggle={toggleCampo}
              onMoveUp={moveCampoUp}
              onMoveDown={moveCampoDown}
            />
          {/each}
        </div>

        <div class="mt-4">
          <h3 class="text-sm font-medium text-[rgb(var(--text-muted))] mb-2">Campos disponibles (inactivos)</h3>
          <div class="flex flex-wrap gap-2">
            {#each camposReporte.filter(c => !c.enabled) as campo}
              <button
                type="button"
                onclick={() => toggleCampo(campo.id)}
                class="px-3 py-1 rounded-lg border border-[rgb(var(--border-primary))] text-sm text-[rgb(var(--text-muted))] hover:border-[rgb(var(--accent-primary))] hover:text-[rgb(var(--accent-primary))]"
              >
                + {campo.label}
              </button>
            {/each}
          </div>
        </div>
      </div>
    {/if}

    <div class="bg-[rgb(var(--card-bg))] border border-[rgb(var(--card-border))] rounded-2xl p-6 shadow-lg">
      <div class="flex items-center gap-2 mb-4">
        <Filter class="w-5 h-5 text-[rgb(var(--accent-primary))]" />
        <h2 class="text-lg font-bold text-[rgb(var(--text-primary))]">Filtros de Búsqueda</h2>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="space-y-1">
          <span class="text-xs font-bold text-[rgb(var(--text-muted))] uppercase tracking-wide">Docentes</span>
          <button
            type="button"
            onclick={() => { showDocenteSelector = true; docenteSearchTerm = '' }}
            class="w-full px-4 py-2 rounded-xl border border-[rgb(var(--border-primary))] bg-[rgb(var(--bg-primary))] text-[rgb(var(--text-primary))] focus:outline-none focus:ring-2 focus:ring-[rgb(var(--accent-primary))] text-left flex justify-between items-center"
          >
            <span class="truncate">
              {filtros.docente.length === 0
                ? 'Todos los docentes'
                : filtros.docente.length === 1
                  ? filtros.docente[0]
                  : `${filtros.docente.length} docentes seleccionados`}
            </span>
            <Users size={16} class="text-[rgb(var(--text-muted))]" />
          </button>
          {#if filtros.docente.length > 0}
            <div class="flex flex-wrap gap-1 mt-1">
              {#each filtros.docente.slice(0, 3) as doc}
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-[rgb(var(--accent-primary))] text-white text-xs">
                  {doc}
                  <button
                    type="button"
                    onclick={() => filtros.docente = filtros.docente.filter(d => d !== doc)}
                    class="hover:text-red-200"
                  >
                    <X size={12} />
                  </button>
                </span>
              {/each}
              {#if filtros.docente.length > 3}
                <span class="px-2 py-0.5 rounded-full bg-[rgb(var(--bg-secondary))] text-[rgb(var(--text-muted))] text-xs">
                  +{filtros.docente.length - 3} más
                </span>
              {/if}
            </div>
          {/if}
        </div>

        <div class="space-y-1">
          <label for="mes" class="text-xs font-bold text-[rgb(var(--text-muted))] uppercase tracking-wide">Mes</label>
          <select
            id="mes"
            bind:value={mesSeleccionado}
            onchange={onMesChange}
            class="w-full px-4 py-2 rounded-xl border border-[rgb(var(--border-primary))] bg-[rgb(var(--bg-primary))] text-[rgb(var(--text-primary))] focus:outline-none focus:ring-2 focus:ring-[rgb(var(--accent-primary))] appearance-none cursor-pointer"
          >
            {#each MESES as m}
              <option value={m.value}>{m.label}</option>
            {/each}
          </select>
        </div>

        <div class="space-y-1">
          <label for="anio" class="text-xs font-bold text-[rgb(var(--text-muted))] uppercase tracking-wide">Año</label>
          <select
            id="anio"
            bind:value={anioSeleccionado}
            onchange={onAnioChange}
            class="w-full px-4 py-2 rounded-xl border border-[rgb(var(--border-primary))] bg-[rgb(var(--bg-primary))] text-[rgb(var(--text-primary))] focus:outline-none focus:ring-2 focus:ring-[rgb(var(--accent-primary))] appearance-none cursor-pointer"
          >
            {#each [2024, 2025, 2026] as year}
              <option value={year}>{year}</option>
            {/each}
          </select>
        </div>

        <div class="space-y-1">
          <label for="fecha_inicio" class="text-xs font-bold text-[rgb(var(--text-muted))] uppercase tracking-wide">Fecha Inicio</label>
          <input
            id="fecha_inicio"
            type="date"
            bind:value={filtros.fechaInicio}
            class="w-full px-4 py-2 rounded-xl border border-[rgb(var(--border-primary))] bg-[rgb(var(--bg-primary))] text-[rgb(var(--text-primary))] focus:outline-none focus:ring-2 focus:ring-[rgb(var(--accent-primary))]"
          />
        </div>

        <div class="space-y-1">
          <label for="fecha_fin" class="text-xs font-bold text-[rgb(var(--text-muted))] uppercase tracking-wide">Fecha Fin</label>
          <input
            id="fecha_fin"
            type="date"
            bind:value={filtros.fechaFin}
            class="w-full px-4 py-2 rounded-xl border border-[rgb(var(--border-primary))] bg-[rgb(var(--bg-primary))] text-[rgb(var(--text-primary))] focus:outline-none focus:ring-2 focus:ring-[rgb(var(--accent-primary))]"
          />
        </div>

        <div class="space-y-1">
          <label for="grado" class="text-xs font-bold text-[rgb(var(--text-muted))] uppercase tracking-wide">Grado</label>
          <select
            id="grado"
            bind:value={filtros.grado}
            class="w-full px-4 py-2 rounded-xl border border-[rgb(var(--border-primary))] bg-[rgb(var(--bg-primary))] text-[rgb(var(--text-primary))] focus:outline-none focus:ring-2 focus:ring-[rgb(var(--accent-primary))] appearance-none cursor-pointer"
          >
            <option value="">Todos los grados</option>
            {#each gradosDisponibles as grado}
              <option value={grado}>{grado}</option>
            {/each}
          </select>
        </div>

        <div class="space-y-1">
          <label for="asignatura" class="text-xs font-bold text-[rgb(var(--text-muted))] uppercase tracking-wide">Asignatura</label>
          <select
            id="asignatura"
            bind:value={filtros.asignatura}
            class="w-full px-4 py-2 rounded-xl border border-[rgb(var(--border-primary))] bg-[rgb(var(--bg-primary))] text-[rgb(var(--text-primary))] focus:outline-none focus:ring-2 focus:ring-[rgb(var(--accent-primary))] appearance-none cursor-pointer"
          >
            <option value="">Todas las asignaturas</option>
            {#each materiasList as mat}
              <option value={mat.materia}>{mat.materia}</option>
            {/each}
          </select>
        </div>
      </div>

      <div class="mt-4 flex justify-end">
        <button
          onclick={limpiarFiltros}
          class="flex items-center gap-2 px-4 py-2 rounded-xl border border-[rgb(var(--border-primary))] text-[rgb(var(--text-primary))] font-medium hover:bg-[rgb(var(--bg-secondary))] transition-colors"
        >
          <X class="w-4 h-4" />
          Limpiar Filtros
        </button>
      </div>
    </div>

    {#if showDocenteSelector}
      <!-- svelte-ignore a11y_interactive_supports_focus -->
      <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        onclick={() => showDocenteSelector = false}
        onkeydown={(e) => { if (e.key === 'Escape') showDocenteSelector = false; }}
        role="dialog"
        aria-modal="true"
        aria-label="Selector de docentes"
        tabindex="0"
      >
        <!-- svelte-ignore a11y_no_noninteractive_element_interactions -->
        <div class="bg-[rgb(var(--bg-primary))] rounded-2xl shadow-2xl w-full max-w-2xl max-h-[80vh] flex flex-col" onclick={(e) => e.stopPropagation()} onkeydown={(e) => e.stopPropagation()} role="document">
          <div class="flex items-center justify-between p-4 border-b border-[rgb(var(--border-primary))]">
            <h3 class="text-lg font-bold text-[rgb(var(--text-primary))]">Seleccionar Docentes</h3>
            <button onclick={() => showDocenteSelector = false} class="p-1 hover:bg-[rgb(var(--bg-secondary))] rounded-lg">
              <X size={20} class="text-[rgb(var(--text-muted))]" />
            </button>
          </div>

          <div class="p-4 border-b border-[rgb(var(--border-primary))]">
            <div class="flex gap-2 mb-3">
              <button
                onclick={() => filtros.docente = [...docentesList]}
                class="px-3 py-1.5 rounded-lg bg-[rgb(var(--bg-secondary))] text-sm hover:bg-[rgb(var(--accent-primary))] hover:text-white transition-colors"
              >
                Todos
              </button>
              <button
                onclick={() => filtros.docente = []}
                class="px-3 py-1.5 rounded-lg bg-[rgb(var(--bg-secondary))] text-sm hover:bg-[rgb(var(--accent-primary))] hover:text-white transition-colors"
              >
                Ninguno
              </button>
              <button
                onclick={() => {
                  const unselected = docentesList.filter(d => !filtros.docente.includes(d))
                  filtros.docente = [...filtros.docente, ...unselected]
                }}
                class="px-3 py-1.5 rounded-lg bg-[rgb(var(--bg-secondary))] text-sm hover:bg-[rgb(var(--accent-primary))] hover:text-white transition-colors"
              >
                Invertir
              </button>
            </div>

            <div class="flex gap-2">
              <input
                type="text"
                bind:value={docenteSearchTerm}
                placeholder="Buscar docente..."
                class="flex-1 px-4 py-2 rounded-xl border border-[rgb(var(--border-primary))] bg-[rgb(var(--bg-primary))] text-[rgb(var(--text-primary))] focus:outline-none focus:ring-2 focus:ring-[rgb(var(--accent-primary))]"
              />
              <div class="flex items-center gap-2">
                <input
                  type="text"
                  id="groupName"
                  placeholder="Nombre del grupo"
                  class="px-3 py-2 rounded-xl border border-[rgb(var(--border-primary))] bg-[rgb(var(--bg-primary))] text-[rgb(var(--text-primary))] text-sm focus:outline-none focus:ring-2 focus:ring-[rgb(var(--accent-primary))] w-40"
                />
                <button
                  onclick={() => {
                    const input = document.getElementById('groupName') as HTMLInputElement
                    if (input?.value && filtros.docente.length > 0) {
                      saveCurrentAsGroup(input.value)
                      input.value = ''
                    }
                  }}
                  disabled={filtros.docente.length === 0}
                  class="px-3 py-2 rounded-xl bg-[rgb(var(--accent-primary))] text-white text-sm disabled:opacity-50 hover:opacity-90 transition-opacity"
                >
                  Guardar
                </button>
              </div>
            </div>

            {#if savedDocenteGroups.length > 0}
              <div class="flex flex-wrap gap-2 mt-3">
                {#each savedDocenteGroups as group}
                  <div class="flex items-center gap-1 px-3 py-1.5 rounded-full bg-[rgb(var(--accent-primary))] text-white text-sm">
                    <button
                      onclick={() => loadGroup(group)}
                      class="hover:underline"
                    >
                      {group.name} ({group.docentes.length})
                    </button>
                    <button
                      onclick={() => deleteGroup(group.name)}
                      class="ml-1 hover:text-red-200"
                    >
                      <X size={12} />
                    </button>
                  </div>
                {/each}
              </div>
            {/if}
          </div>

          <div class="flex-1 overflow-auto p-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-1">
              {#each filteredDocentes as doc}
                <label class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-[rgb(var(--bg-secondary))] cursor-pointer">
                  <input
                    type="checkbox"
                    checked={filtros.docente.includes(doc)}
                    onchange={() => {
                      if (filtros.docente.includes(doc)) {
                        filtros.docente = filtros.docente.filter(d => d !== doc)
                      } else {
                        filtros.docente = [...filtros.docente, doc]
                      }
                    }}
                    class="w-5 h-5 rounded border-[rgb(var(--border-primary))] accent-[rgb(var(--accent-primary))]"
                  />
                  <span class="text-sm text-[rgb(var(--text-primary))]">{doc}</span>
                </label>
              {/each}
            </div>
            {#if filteredDocentes.length === 0 && docenteSearchTerm}
              <p class="text-center text-[rgb(var(--text-muted))] py-8">No se encontraron docentes</p>
            {/if}
          </div>

          <div class="p-4 border-t border-[rgb(var(--border-primary))] flex justify-between items-center">
            <span class="text-sm text-[rgb(var(--text-muted))]">
              {filtros.docente.length} de {docentesList.length} seleccionados
            </span>
            <button
              onclick={() => { showDocenteSelector = false; aplicarFiltros() }}
              class="px-6 py-2 rounded-xl bg-[rgb(var(--accent-primary))] text-white font-medium hover:opacity-90 transition-opacity"
            >
              Aplicar
            </button>
          </div>
        </div>
      </div>
    {/if}

    <div class="flex items-center justify-between">
      <div class="flex items-center gap-4">
        <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-[rgb(var(--card-bg))] border border-[rgb(var(--card-border))]">
          <Clock class="w-5 h-5 text-[rgb(var(--accent-primary))]" />
          <span class="text-sm font-medium text-[rgb(var(--text-primary))]">
            Total: <span class="font-bold text-[rgb(var(--accent-primary))]">{registrosFiltrados.length}</span> registros
          </span>
          <span class="text-sm text-[rgb(var(--text-muted))]">|</span>
          <span class="text-sm font-medium text-[rgb(var(--text-primary))]">
            Horas: <span class="font-bold text-emerald-600">{getTotalHoras().toFixed(2)}</span>
          </span>
          <span class="text-sm text-[rgb(var(--text-muted))]">|</span>
          <span class="text-sm font-medium text-[rgb(var(--text-primary))]">
            Días del mes: <span class="font-bold text-[rgb(var(--accent-primary))]">{getDiasDelMes().length}</span>
          </span>
        </div>
      </div>

      <div class="flex items-center gap-2 flex-wrap">
        <button
          onclick={generarReportePDF}
          disabled={isGeneratingPdf || registrosFiltrados.length === 0}
          class="flex items-center gap-2 px-4 py-2 rounded-xl bg-red-500 text-white font-medium hover:bg-red-600 transition-colors disabled:opacity-50"
        >
          {#if isGeneratingPdf}
            <Loader2 class="w-4 h-4 animate-spin" />
            Generando PDF...
          {:else}
            <FileText class="w-4 h-4" />
            Exportar PDF
          {/if}
        </button>
        <button
          onclick={generarReporteExcel}
          disabled={isGeneratingExcel || registrosFiltrados.length === 0}
          class="flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-500 text-white font-medium hover:bg-blue-600 transition-colors disabled:opacity-50"
        >
          {#if isGeneratingExcel}
            <Loader2 class="w-4 h-4 animate-spin" />
            Generando...
          {:else}
            <FileSpreadsheet class="w-4 h-4" />
            Excel Simple
          {/if}
        </button>
        <button
          onclick={generarReporteExcelPorDocente}
          disabled={isGeneratingExcel || registrosFiltrados.length === 0}
          class="flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-500 text-white font-medium hover:bg-emerald-600 transition-colors disabled:opacity-50"
        >
          {#if isGeneratingExcel}
            <Loader2 class="w-4 h-4 animate-spin" />
            Generando...
          {:else}
            <User class="w-4 h-4" />
            Excel por Docente
          {/if}
        </button>
      </div>
    </div>

    {#if isLoadingData || isLoadingRecords}
      <div class="flex items-center justify-center py-12">
        <Loader2 class="w-8 h-8 animate-spin text-[rgb(var(--accent-primary))]" />
        <span class="ml-3 text-[rgb(var(--text-muted))]">Cargando registros...</span>
      </div>
    {:else if registrosFiltrados.length === 0}
      <div class="bg-[rgb(var(--card-bg))] border border-[rgb(var(--card-border))] rounded-2xl p-12 text-center shadow-lg">
        <Clock class="w-16 h-16 mx-auto mb-4 text-[rgb(var(--text-muted))]" />
        <h3 class="text-lg font-bold text-[rgb(var(--text-primary))] mb-2">No hay registros</h3>
        <p class="text-sm text-[rgb(var(--text-muted))]">No se encontraron horas extras con los filtros seleccionados.</p>
      </div>
    {:else}
      <div class="bg-[rgb(var(--card-bg))] border border-[rgb(var(--card-border))] rounded-2xl overflow-hidden shadow-lg">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-[rgb(var(--bg-secondary))]">
              <tr>
                {#each getEnabledCampos() as campo}
                  <th class="px-3 py-3 text-left text-xs font-bold text-[rgb(var(--text-muted))] uppercase tracking-wide">{campo.label}</th>
                {/each}
                <th class="px-3 py-3 text-center text-xs font-bold text-[rgb(var(--text-muted))] uppercase tracking-wide">Horas</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[rgb(var(--border-primary))]">
              {#each registrosFiltrados as reg}
                <tr class="hover:bg-[rgb(var(--bg-secondary))] transition-colors">
                  {#each getEnabledCampos() as campo}
                    <td class="px-3 py-3 text-sm text-[rgb(var(--text-primary))]">
                      {#if campo.id === 'cedula'}
                        {reg.cedula}
                      {:else if campo.id === 'docente'}
                        {reg.docente}
                      {:else if campo.id === 'fecha'}
                        {reg.fecha}
                      {:else if campo.id === 'asignatura'}
                        {reg.asignatura}
                      {:else if campo.id === 'grado'}
                        {reg.gradoAtendido}
                      {:else if campo.id === 'escalafon'}
                        {reg.escalafon}
                      {:else if campo.id === 'actividad'}
                        {reg.actividad}
                      {:else if campo.id === 'horaEntrada'}
                        {reg.horaEntrada}
                      {:else if campo.id === 'horaSalida'}
                        {reg.horaSalida}
                      {:else if campo.id === 'observaciones'}
                        {reg.observaciones || '-'}
                      {/if}
                    </td>
                  {/each}
                  <td class="px-3 py-3 text-sm font-mono font-bold text-center text-[rgb(var(--accent-primary))]">{reg.horasExtras.toFixed(2)}</td>
                </tr>
              {/each}
            </tbody>
          </table>
        </div>
      </div>
    {/if}
  </div>
</div>

<style>
  select option {
    background: rgb(var(--bg-primary));
    color: rgb(var(--text-primary));
  }
</style>