"use client";

import { useEffect, useState, useCallback } from "react";
import { ClipboardCheck, Shredder, Filter, Plus, ChevronLeft, ChevronRight, CalendarCheck, FlaskConical, ChevronDown, OctagonAlert, ArrowUpDown, ArrowUp, ArrowDown } from "lucide-react";
import { fetchSamples, updateSample, checkSession, type Sample } from "@/lib/api";
import Header from "@/app/components/header";
import { useRouter } from "next/navigation";

const ERROR_TRANSLATIONS: Record<string, string> = {
  "Sample not found": "Amostra não encontrada.",
  'sampleTechnician is required to set status to em_analise': "É necessário informar o responsável técnico para iniciar a análise.",
  'Sample must have a conclusion date to be set to "concluida".': "A amostra precisa de uma data de conclusão para ser concluída.",
  "Sample conclusion date must be equal or greater than sample receival date.": "A data de conclusão deve ser igual ou posterior à data de recebimento.",
  'Sample must have a conclusion date to be set to "rejeitada".': "A amostra precisa de uma data de conclusão para ser rejeitada.",
  'Missing required field: "sampleStatus" or "sampleTechnician".': 'Campo obrigatório não informado: "status" ou "responsável técnico".',
};

function translateError(msg: string): string {
  return ERROR_TRANSLATIONS[msg] ?? msg;
}

// cores - status
const STATUS_MAP: Record<string, { label: string; color: string; bg: string }> = {
  recebida: { label: "Recebida", color: "text-yellow-700", bg: "bg-yellow-200/40" },
  em_analise: { label: "Em análise", color: "text-blue-700", bg: "bg-blue-100/50" },
  concluida: { label: "Concluída", color: "text-green-700", bg: "bg-green-100/75" },
  rejeitada: { label: "Rejeitada", color: "text-red-600", bg: "bg-red-100" },
};

const TYPE_MAP: Record<string, string> = {
  agua: "Água",
  ar: "Ar",
  efluente: "Efluente",
  solo: "Solo",
};

const STATUS_ORDER: Record<string, number> = {
  concluida: 0,
  em_analise: 1,
  recebida: 2,
  rejeitada: 3,
};

type SortKey = "sampleCode" | "sampleType" | "sampleReceivalDate" | "sampleTechnician" | "sampleStatus";

const PER_PAGE = 12; // amostras

export default function Home() {
  const router = useRouter();

  const [samples, setSamples] = useState<Sample[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  const [page, setPage] = useState(1);

  // filtros
  const [searchCode, setSearchCode] = useState("");
  const [searchType, setSearchType] = useState("");
  const [filterStatus, setFilterStatus] = useState("");

  // filtros aplicados
  const [appliedFilterStatus, setAppliedFilterStatus] = useState("");
  const [appliedSearchType, setAppliedSearchType] = useState("");
  const [appliedSearchCode, setAppliedSearchCode] = useState("");

  const [sortKey, setSortKey] = useState<SortKey | null>(null);
  const [sortAsc, setSortAsc] = useState(true);

  const [openDropdown, setOpenDropdown] = useState<number | null>(null);
  const [dropdownPos, setDropdownPos] = useState<{ top: number; right: number }>({ top: 0, right: 0 });

  // definir resp. em amostra existente
  const [modalSample, setModalSample] = useState<Sample | null>(null);
  const [technicianName, setTechnicianName] = useState("");
  const [modalError, setModalError] = useState("");
  const [modalLoading, setModalLoading] = useState(false);
  const [isClosingModal, setIsClosingModal] = useState(false);

  // data de conclusao
  const [conclusionModalSample, setConclusionModalSample] = useState<Sample | null>(null);
  const [conclusionDate, setConclusionDate] = useState(() => new Date().toISOString().split("T")[0]);
  const [conclusionLoading, setConclusionLoading] = useState(false);
  const [isClosingConclusionModal, setIsClosingConclusionModal] = useState(false);

  // rejeicao
  const [rejectModalSample, setRejectModalSample] = useState<Sample | null>(null);
  const [rejectDate, setRejectDate] = useState(() => new Date().toISOString().split("T")[0]);
  const [rejectLoading, setRejectLoading] = useState(false);
  const [isClosingRejectModal, setIsClosingRejectModal] = useState(false);

  function closeModal() {
    setIsClosingModal(true);
    setTimeout(() => {
      setModalSample(null);
      setIsClosingModal(false);
    }, 180);
  }

  function closeConclusionModal() {
    setIsClosingConclusionModal(true);
    setTimeout(() => {
      setConclusionModalSample(null);
      setIsClosingConclusionModal(false);
    }, 180);
  }

  function closeRejectModal() {
    setIsClosingRejectModal(true);
    setTimeout(() => {
      setRejectModalSample(null);
      setIsClosingRejectModal(false);
    }, 180);
  }

  const loadSamples = useCallback(async (overrides?: { searchCode?: string; searchType?: string }) => {
    setLoading(true);
    setError("");
    try {
      const params: { code?: string; search?: string; type?: string } = {};
      const code = overrides?.searchCode ?? appliedSearchCode;
      const type = overrides?.searchType ?? appliedSearchType;
      if (code) params.search = code;
      if (type) params.type = type;
      const data = await fetchSamples(Object.keys(params).length ? params : undefined);
      setSamples(data);
    } catch {
      setError("Erro ao carregar amostras.");
    } finally {
      setLoading(false);
    }
  }, [appliedSearchCode, appliedSearchType]);

  useEffect(() => {
    checkSession().then((ok) => {
      if (!ok) router.push("/login");
      else loadSamples();
    });
  }, []);

  // ordenacao e paginacao
  const filtered = appliedFilterStatus
    ? samples.filter((s) => s.sampleStatus === appliedFilterStatus)
    : samples;

  const sorted = [...filtered].sort((a, b) => {
    if (!sortKey) return 0;
    let cmp = 0;
    if (sortKey === "sampleStatus") {
      cmp = (STATUS_ORDER[a.sampleStatus] ?? 0) - (STATUS_ORDER[b.sampleStatus] ?? 0);
    } else if (sortKey === "sampleReceivalDate") {
      cmp = a.sampleReceivalDate.localeCompare(b.sampleReceivalDate);
    } else if (sortKey === "sampleCode") {
      const numA = parseInt(a.sampleCode.replace(/\D/g, ""), 10) || 0;
      const numB = parseInt(b.sampleCode.replace(/\D/g, ""), 10) || 0;
      cmp = numA !== numB ? numA - numB : a.sampleCode.localeCompare(b.sampleCode);
    } else {
      cmp = (a[sortKey] ?? "").localeCompare(b[sortKey] ?? "");
    }
    return sortAsc ? cmp : -cmp;
  });

  const totalPages = Math.max(1, Math.ceil(sorted.length / PER_PAGE));
  const paged = sorted.slice((page - 1) * PER_PAGE, page * PER_PAGE);
  const paddedPaged = [...paged, ...Array.from({ length: PER_PAGE - paged.length }, () => null)];

  const stats = {
    total: samples.length,
    recebida: samples.filter((s) => s.sampleStatus === "recebida").length,
    em_analise: samples.filter((s) => s.sampleStatus === "em_analise").length,
    concluida: samples.filter((s) => s.sampleStatus === "concluida").length,
    rejeitada: samples.filter((s) => s.sampleStatus === "rejeitada").length,
  };

  // ordenacao
  function handleSort(key: SortKey) {
    if (sortKey === key) {
      setSortAsc(!sortAsc);
    } else {
      setSortKey(key);
      setSortAsc(true);
    }
  }

  function SortIcon({ col }: { col: SortKey }) {
    if (sortKey !== col) return <ArrowUpDown className="w-3 h-3 ml-1 opacity-40" />;
    return sortAsc
      ? <ArrowUp className="w-3 h-3 ml-1 text-primary" />
      : <ArrowDown className="w-3 h-3 ml-1 text-primary" />;
  }

  function handleFilter() {
    setAppliedFilterStatus(filterStatus);
    setAppliedSearchType(searchType);
    setAppliedSearchCode(searchCode);
    setPage(1);
    loadSamples({ searchCode, searchType });
  }

  async function sampleNextStatus(sample: Sample, conclusion?: string) {
    const statusOrder = ["recebida", "em_analise", "concluida"];
    const idx = statusOrder.indexOf(sample.sampleStatus);
    if (idx < 0 || idx >= statusOrder.length - 1) return;

    const nextStatus = statusOrder[idx + 1];
    const body: { sampleCode: string; sampleStatus: string; sampleConclusionDate?: string } = {
      sampleCode: sample.sampleCode,
      sampleStatus: nextStatus,
    };

    if (nextStatus === "concluida") {
      body.sampleConclusionDate = conclusion || new Date().toISOString().split("T")[0];
    }

    try {
      await updateSample(body);
      loadSamples();
    } catch (e: unknown) {
      setError(translateError(e instanceof Error ? e.message : "Erro desconhecido ao avançar status da amostra."));
    }
  }

  async function handleConclusionConfirm() {
    if (!conclusionModalSample) return;
    setConclusionLoading(true);
    try {
      await sampleNextStatus(conclusionModalSample, conclusionDate);
      closeConclusionModal();
    } catch (e: unknown) {
      setError(translateError(e instanceof Error ? e.message : "Erro ao salvar"));
    } finally {
      setConclusionLoading(false);
    }
  }

  async function handleRejectConfirm() {
    if (!rejectModalSample) return;
    setRejectLoading(true);
    try {
      await updateSample({
        sampleCode: rejectModalSample.sampleCode,
        sampleStatus: "rejeitada",
        sampleConclusionDate: rejectDate,
      });
      closeRejectModal();
      loadSamples();
    } catch (e: unknown) {
      setError(translateError(e instanceof Error ? e.message : "Erro ao rejeitar amostra"));
    } finally {
      setRejectLoading(false);
    }
  }

  //confirmar responsavel
  async function handleTechnicianModalConfirm() {
    if (!modalSample || !technicianName.trim()) return;

    setModalLoading(true);
    setModalError("");

    try {
      await updateSample({
        sampleCode: modalSample.sampleCode,
        sampleTechnician: technicianName.trim(),
      });
      await sampleNextStatus(modalSample);
      closeModal();
    } catch (e: unknown) {
      setModalError(translateError(e instanceof Error ? e.message : "Erro ao salvar"));
    } finally {
      setModalLoading(false);
    }
  }

  // rejeitar amostra e preencher data de conclusao
  function handleReject(sample: Sample) {
    setRejectModalSample(sample);
    setRejectDate(new Date().toISOString().split("T")[0]);
  }

  // yyyy-mm-dd -> dd/mm/yyyy
  function formatDate(d: string) {
    const [y, m, day] = d.split("-");
    return `${day}/${m}/${y}`;
  }

  function openActionDropdown(e: React.MouseEvent, sampleId: number) {
    const rect = (e.currentTarget as HTMLElement).getBoundingClientRect();
    setDropdownPos({ top: rect.bottom + 4, right: window.innerWidth - rect.right });
    setOpenDropdown(openDropdown === sampleId ? null : sampleId);
  }

  // iniciar analise / concluir
  function handleSampleNextStep(sample: Sample) {
    setOpenDropdown(null);
    if (!sample.sampleTechnician) {
      setModalSample(sample);
      setTechnicianName("");
      setModalError("");
    } else if (sample.sampleStatus === "em_analise") {
      setConclusionModalSample(sample);
      setConclusionDate(new Date().toISOString().split("T")[0]);
    } else {
      sampleNextStatus(sample);
    }
  }

  return (
    <>
      <Header />
      <div className="flex gap-6 p-6 max-w-[1750px] mx-auto flex-1 min-h-0">
        <div className="flex-1 flex flex-col min-h-0">
          {error && (
            <div className="bg-red-50 text-red-700 p-3 rounded-lg mb-4 text-sm">{error}</div>
          )}

          <div className="bg-white rounded-xl overflow-hidden">
            <table className="w-full text-sm table-fixed">
              <thead>
                <tr className="border-b border-border text-left text-gray-500 text-xs h-10 uppercase">
                  <th className="px-4 py-3 w-[14%]">
                    <button onClick={() => handleSort("sampleCode")} className="cursor-pointer flex items-center uppercase">
                      Código <SortIcon col="sampleCode" />
                    </button>
                  </th>
                  <th className="px-4 py-3 w-[10%]">
                    <button onClick={() => handleSort("sampleType")} className="cursor-pointer flex items-center uppercase">
                      Análise <SortIcon col="sampleType" />
                    </button>
                  </th>
                  <th className="px-4 py-3 w-[10%]">
                    <button onClick={() => handleSort("sampleReceivalDate")} className="cursor-pointer flex items-center uppercase">
                      Coleta <SortIcon col="sampleReceivalDate" />
                    </button>
                  </th>
                  <th className="px-4 py-3 w-[18%]">
                    <button onClick={() => handleSort("sampleTechnician")} className="cursor-pointer flex items-center uppercase">
                      Responsável Técnico <SortIcon col="sampleTechnician" />
                    </button>
                  </th>
                  <th className="px-4 py-3 w-[12%]">
                    <button onClick={() => handleSort("sampleStatus")} className="cursor-pointer flex items-center uppercase">
                      Status <SortIcon col="sampleStatus" />
                    </button>
                  </th>
                  <th className="px-4 py-3 w-[24%]">Histórico da Amostra</th>
                  <th className="px-4 py-3 text-right w-[13%]">Ação</th>
                </tr>
              </thead>
              <tbody key={page} className="animate-fade-in">
                {loading ? (
                  // skeleton
                  Array.from({ length: PER_PAGE }).map((_, i) => (
                      <tr key={`loading-${i}`} className="border-b border-border last:border-b-0">
                        <td className="px-4 py-3 h-12"><div className="h-3 bg-gray-200 rounded animate-pulse w-16" /></td>
                        <td className="px-4 py-3 h-12"><div className="h-3 bg-gray-200 rounded animate-pulse w-14" /></td>
                        <td className="px-4 py-3 h-12"><div className="h-3 bg-gray-200 rounded animate-pulse w-20" /></td>
                        <td className="px-4 py-3 h-12"><div className="h-3 bg-gray-200 rounded animate-pulse w-28" /></td>
                        <td className="px-4 py-3 h-12"><div className="h-3 bg-gray-200 rounded animate-pulse w-16" /></td>
                        <td className="px-4 py-3 h-12"><div className="h-3 bg-gray-200 rounded animate-pulse w-24" /></td>
                        <td className="px-4 py-3 h-12"><div className="h-3 bg-gray-200 rounded animate-pulse w-20" /></td>
                    </tr>
                  ))
                ) : (
                  paddedPaged.map((s, i) => {
                    if (!s) {
                      return (
                        <tr key={`empty-${i}`} className="border-b border-border last:border-b-0">
                          <td className="px-4 py-3 h-12">&nbsp;</td>
                          <td className="px-4 py-3 h-12">&nbsp;</td>
                          <td className="px-4 py-3 h-12">&nbsp;</td>
                          <td className="px-4 py-3 h-12">&nbsp;</td>
                          <td className="px-4 py-3 h-12">&nbsp;</td>
                          <td className="px-4 py-3 h-12">&nbsp;</td>
                          <td className="px-4 py-3 h-12">&nbsp;</td>
                        </tr>
                      );
                    }
                    const st = STATUS_MAP[s.sampleStatus] || STATUS_MAP.recebida;
                    return (
                      <tr key={s.id} className="border-b border-border last:border-b-0 hover:bg-gray-50 h-12">
                        <td className="px-4 py-0 font-medium truncate">{s.sampleCode}</td>
                        <td className="px-4 py-0 text-gray-600 truncate">{TYPE_MAP[s.sampleType] || s.sampleType}</td>
                        <td className="px-4 py-0 truncate">{formatDate(s.sampleReceivalDate)}</td>
                        <td className="px-4 py-0 text-gray-600 truncate">{s.sampleTechnician || <span className="text-gray-400/80">(não informado)</span>}</td>
                        <td className="px-4 py-0 truncate">
                          <span className={`inline-flex items-center gap-1.5 px-3 py-1 border-1 rounded-full text-xs font-medium ${st.bg} ${st.color}`}>
                            <span className="w-1.5 h-1.5 rounded-full bg-current" />
                            {st.label}
                          </span>
                        </td>
                        <td className="px-4 py-0 text-gray-500 text-xs truncate">
                          Recebimento: {s.sampleTechnician ? formatDate(s.sampleReceivalDate) : "-"}
                          {" | "}
                          Conclusão: {s.sampleConclusionDate ? formatDate(s.sampleConclusionDate) : "-"}
                        </td>
                        <td className="px-4 py-0">
                          <div className="flex items-center justify-end gap-2">
                            {(s.sampleStatus === "recebida" || s.sampleStatus === "em_analise") && (
                              <div className="relative">
                                <button
                                  onClick={(e) => openActionDropdown(e, s.id)}
                                  className="text-xs text-gray-500 hover:text-primary rounded-lg px-3 py-1.5 bg-gray-200/70 hover:bg-gray-300 transition-all duration-250 cursor-pointer"
                                >
                                  Avançar status <ChevronDown className="h-4 w-4 mb-0.25 inline-flex" />
                                </button>
                                {openDropdown === s.id && (
                                  <>
                                    {/* fechar dropdown clicando fora */}
                                    <div className="fixed inset-0 z-50" onClick={() => setOpenDropdown(null)} />
                                    {/* posicao dropdown */}
                                    <div className="fixed w-44 bg-white border border-border rounded-lg shadow-lg z-999 py-1 origin-top-right animate-scale-in" style={{ top: dropdownPos.top, right: dropdownPos.right }}>
                                      {s.sampleStatus === "recebida" ? (
                                        <button
                                          onClick={() => handleSampleNextStep(s)}
                                          className="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-200 flex items-center gap-2 cursor-pointer"
                                        >
                                          <FlaskConical className="w-4 h-4 text-primary" />
                                          Iniciar análise
                                        </button>
                                      ) : (
                                        <button
                                          onClick={() => handleSampleNextStep(s)}
                                          className="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-200 flex items-center gap-2 cursor-pointer"
                                        >
                                          <ClipboardCheck className="w-4 h-4 text-primary" />
                                          Concluir amostra
                                        </button>
                                      )}
                                      <button
                                        onClick={() => { setOpenDropdown(null); handleReject(s); }}
                                        className="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-600 hover:text-white flex items-center gap-2 cursor-pointer"
                                      >
                                        <Shredder className="w-4 h-4" />
                                        Rejeitar amostra
                                      </button>
                                    </div>
                                  </>
                                )}
                              </div>
                            )}
                          </div>
                        </td>
                      </tr>
                    );
                  })
                )}
              </tbody>
            </table>
          </div>

          <div className="flex items-center justify-between mt-4 text-sm text-gray-500">
            <span className="ml-1">
              Página {page} de {totalPages} | {filtered.length} amostras encontradas
            </span>
            <div className="flex gap-2">
              <button
                onClick={() => setPage((p) => Math.max(1, p - 1))}
                disabled={page <= 1}
                className="w-8 h-8 rounded-lg border border-border flex items-center justify-center border-gray-400 hover:border-black/25 hover:text-white hover:bg-gray-400/80 disabled:opacity-45 cursor-pointer disabled:cursor-not-allowed transition-all"
              >
                <ChevronLeft className="w-4 h-4" />
              </button>
              <button
                onClick={() => setPage((p) => Math.min(totalPages, p + 1))}
                disabled={page >= totalPages}
                className="w-8 h-8 rounded-lg border border-border flex items-center justify-center border-gray-400 hover:border-black/25 hover:text-white hover:bg-gray-400/80 disabled:opacity-45 cursor-pointer disabled:cursor-not-allowed transition-all"
              >
                <ChevronRight className="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>

        <div className="w-72 flex-shrink-0 space-y-4">
          <div className="bg-white rounded-xl p-4 grid grid-cols-2 gap-4">
            <StatCard label="Total" count={stats.total} color="bg-gray-800" />
            <StatCard label="Recebida" count={stats.recebida} color="bg-yellow-500" />
            <StatCard label="Em análise" count={stats.em_analise} color="bg-blue-500" />
            <StatCard label="Concluída" count={stats.concluida} color="bg-purple-500" />
            <StatCard label="Rejeitada" count={stats.rejeitada} color="bg-red-500" />
          </div>

          <div className="bg-white rounded-xl p-4 space-y-3">
            <h3 className="text-sm font-semibold text-primary">Buscar amostras</h3>
            <input
              type="text"
              placeholder="Código"
              value={searchCode}
              onChange={(e) => setSearchCode(e.target.value)}
              className="w-full border border-border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20"
            />
            <select
              value={filterStatus}
              onChange={(e) => setFilterStatus(e.target.value)}
              className="w-full border border-border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20"
            >
              <option value="">Todos os status</option>
              <option value="recebida">Recebida</option>
              <option value="em_analise">Em análise</option>
              <option value="concluida">Concluída</option>
              <option value="rejeitada">Rejeitada</option>
            </select>
            <select
              value={searchType}
              onChange={(e) => setSearchType(e.target.value)}
              className="w-full border border-border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20"
            >
              <option value="">Todos os tipos</option>
              <option value="agua">Água</option>
              <option value="ar">Ar</option>
              <option value="efluente">Efluente</option>
              <option value="solo">Solo</option>
            </select>
            <button
              onClick={handleFilter}
              className="w-full bg-primary text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-primary-light hover:scale-102 transition-all duration-250 flex items-center justify-center gap-2 cursor-pointer"
            >
              <Filter className="w-4 h-4" />
              Filtrar
            </button>
          </div>
          <button
            onClick={() => router.push("/dashboard/new-sample")}
            className="w-full bg-primary text-white rounded-xl px-4 py-3 text-sm font-medium hover:bg-primary-light hover:scale-102 transition-all duration-250 flex items-center justify-center gap-2 cursor-pointer"
          >
            <Plus className="w-5 h-5" />
            Cadastrar nova amostra
          </button>
        </div>

        {/* modal responsavel */}
        {modalSample && (
          <div
            className={`fixed inset-0 bg-black/50 flex items-center justify-center z-50 ${isClosingModal ? "animate-fade-out" : "animate-fade-in"}`}
            onClick={closeModal}
          >
            <div
              className={`bg-white rounded-xl p-5 !pt-4 w-full max-w-sm origin-center ${isClosingModal ? "animate-scale-out" : "animate-scale-in"}`}
              onClick={(e) => e.stopPropagation()}
            >
              <h3 className="font-semibold text-primary mb-2"><OctagonAlert className="mb-0.75 h-5 w-5 inline-flex text-red-600 mr-2" />Responsável técnico não informado</h3>
              <p className="text-xs text-gray-500 mb-4">
                Informe o nome do responsável técnico para avançar o status da amostra <strong>{modalSample.sampleCode}</strong>.
              </p>

              {modalError && (
                <div className="bg-red-50 text-red-700 p-2 rounded-lg mb-3 text-xs">{modalError}</div>
              )}

              <input
                type="text"
                value={technicianName}
                onChange={(e) => setTechnicianName(e.target.value)}
                placeholder="Nome do responsável"
                autoFocus
                className="w-full border border-border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 mb-4"
              />

              <div className="flex justify-end gap-2">
                <button
                  onClick={closeModal}
                  className="px-4 py-2 text-sm text-gray-500 hover:bg-gray-200 rounded-lg transition-all duration-200 cursor-pointer"
                >
                  Cancelar
                </button>
                <button
                  onClick={handleTechnicianModalConfirm}
                  disabled={!technicianName.trim() || modalLoading}
                  className="px-4 py-2 text-sm bg-primary-light text-white rounded-lg hover:scale-104 transition-all disabled:opacity-70 cursor-pointer"
                >
                  {modalLoading ? "Salvando..." : "Confirmar"}
                </button>
              </div>
            </div>
          </div>
        )}

        {/* modal conclusao */}
        {conclusionModalSample && (
          <div
            className={`fixed inset-0 bg-black/40 flex items-center justify-center z-50 ${isClosingConclusionModal ? "animate-fade-out" : "animate-fade-in"}`}
            onClick={closeConclusionModal}
          >
            <div
              className={`bg-white rounded-xl shadow-lg p-5 !pt-4 w-full max-w-sm origin-center ${isClosingConclusionModal ? "animate-scale-out" : "animate-scale-in"}`}
              onClick={(e) => e.stopPropagation()}
            >
              <h3 className="font-semibold text-primary mb-2"><CalendarCheck className="mb-1 h-5 w-5 inline-flex text-blue-500 mr-2" />Data de conclusão</h3>
              <p className="text-xs text-gray-500 mb-4">
                Informe a data de conclusão da amostra <strong>{conclusionModalSample.sampleCode}</strong>.
              </p>

              <input
                type="date"
                value={conclusionDate}
                onChange={(e) => setConclusionDate(e.target.value)}
                className="w-full border border-border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 mb-4"
              />

              <div className="flex justify-end gap-2">
                <button
                  onClick={closeConclusionModal}
                  className="px-4 py-2 text-sm text-gray-500 hover:bg-gray-200 rounded-lg transition-all cursor-pointer"
                >
                  Cancelar
                </button>
                <button
                  onClick={handleConclusionConfirm}
                  disabled={!conclusionDate || conclusionLoading}
                  className="px-4 py-2 text-sm bg-primary-light text-white rounded-lg hover:scale-104 transition-all disabled:opacity-70 cursor-pointer"
                >
                  {conclusionLoading ? "Salvando..." : "Confirmar"}
                </button>
              </div>
            </div>
          </div>
        )}

        {/* modal rejeicao */}
        {rejectModalSample && (
          <div className={`fixed inset-0 bg-black/40 flex items-center justify-center z-50 ${isClosingRejectModal ? "animate-fade-out" : "animate-fade-in"}`}>
            <div className={`bg-white rounded-xl shadow-lg p-5 !pt-4 w-full max-w-sm origin-center ${isClosingRejectModal ? "animate-scale-out" : "animate-scale-in"}`}>
              <h3 className="font-semibold text-primary mb-2"><Shredder className="mb-1 h-5 w-5 inline-flex text-red-600 mr-2" />Rejeitar amostra</h3>
              <p className="text-xs text-gray-500 mb-4">
                Informe a data de conclusão para rejeitar a amostra <strong>{rejectModalSample.sampleCode}</strong>.
              </p>

              <input
                type="date"
                value={rejectDate}
                onChange={(e) => setRejectDate(e.target.value)}
                className="w-full border border-border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 mb-4"
              />

              <div className="flex justify-end gap-2">
                <button
                  onClick={closeRejectModal}
                  className="px-4 py-2 text-sm text-gray-500 hover:bg-gray-200 rounded-lg transition-all cursor-pointer"
                >
                  Cancelar
                </button>
                <button
                  onClick={handleRejectConfirm}
                  disabled={!rejectDate || rejectLoading}
                  className="px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:scale-104 transition-all disabled:opacity-70 cursor-pointer"
                >
                  {rejectLoading ? "Salvando..." : "Confirmar"}
                </button>
              </div>
            </div>
          </div>
        )}
      </div>
    </>
  );
}

function StatCard({ label, count, color }: { label: string; count: number; color: string }) {
  return (
    <div>
      <p className="text-xs text-gray-500">{label}</p>
      <div className="flex items-center gap-2 mt-1">
        <span className={`w-2 h-2 rounded-full ${color}`} />
        <span className="text-xl font-bold">{count}</span>
        <span className="text-xs text-gray-400">
          {count === 1 ? "amostra" : "amostras"}
        </span>
      </div>
    </div>
  );
}
