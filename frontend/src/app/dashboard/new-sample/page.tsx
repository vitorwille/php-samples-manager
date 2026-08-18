"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { createSample } from "@/lib/api";
import Header from "@/app/components/header";
import { ArrowLeft, FlaskConical, User, Calendar, TagPlus } from "lucide-react";

export default function NewSamplePage() {
  const router = useRouter();
  const [sampleType, setSampleType] = useState("");
  const [technician, setTechnician] = useState("");
  const [receivalDate, setReceivalDate] = useState(() => new Date().toISOString().split("T")[0]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setError("");
    setLoading(true);

    try {
      await createSample({
        sampleType,
        sampleTechnician: technician || undefined,
        sampleReceivalDate: receivalDate,
      });
      router.push("/dashboard");
    } catch (e: unknown) {
      setError(e instanceof Error ? e.message : "Erro ao criar amostra");
    } finally {
      setLoading(false);
    }
  }

  return (
    <>
      <Header />
      <div className="min-w-[550px] mx-auto p-6">
        <button
          onClick={() => router.push("/dashboard")}
          className="flex items-center gap-1 text-sm text-gray-500 hover:text-primary mb-4 transition-colors cursor-pointer"
        >
          <ArrowLeft className="w-4 h-4" />
          Voltar
        </button>

        <div className="bg-white rounded-xl border border-border p-6">
          <h1 className="text-xl font-bold text-primary mb-1 flex items-center gap-2">
            <TagPlus className="w-5 h-5 text-primary" />
            Cadastrar nova amostra
          </h1>
          <p className="text-xs text-gray-500 mb-6">Preencha os dados abaixo para cadastrar uma nova amostra.</p>

          {error && (
            <div className="bg-red-50 border border-red-100 text-red-700 p-3 rounded-lg text-xs mb-4">{error}</div>
          )}

          <form onSubmit={handleSubmit} className="space-y-4">
            <div>
              <label className="text-[11px] font-semibold text-slate-600 tracking-wider uppercase mb-1.5 flex items-center gap-1.5">
                <FlaskConical className="w-3.5 h-3.5 text-slate-400" /> Tipo de amostra
              </label>
              <select
                value={sampleType}
                onChange={(e) => setSampleType(e.target.value)}
                required
                className="w-full border border-border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20"
              >
                <option value="">Selecione o tipo</option>
                <option value="agua">Água</option>
                <option value="ar">Ar</option>
                <option value="solo">Solo</option>
                <option value="efluente">Efluente</option>
              </select>
            </div>

            <div>
              <label className="text-[11px] font-semibold text-slate-600 tracking-wider uppercase mb-1.5 flex items-center gap-1.5">
                <User className="w-3.5 h-3.5 text-slate-400" /> Responsável técnico
              </label>
              <input
                type="text"
                value={technician}
                onChange={(e) => setTechnician(e.target.value)}
                className="w-full border border-border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20"
                placeholder="Nome do responsável (opcional)"
              />
            </div>

            <div>
              <label className="text-[11px] font-semibold text-slate-600 tracking-wider uppercase mb-1.5 flex items-center gap-1.5">
                <Calendar className="w-3.5 h-3.5 text-slate-400" /> Data de recebimento
              </label>
              <input
                type="date"
                value={receivalDate}
                onChange={(e) => setReceivalDate(e.target.value)}
                required
                className="w-full border border-border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20"
              />
            </div>

            <div className="flex justify-end gap-2 pt-2">
              <button
                type="button"
                onClick={() => router.push("/dashboard")}
                className="px-4 py-2 text-sm text-gray-500 hover:bg-gray-200 rounded-lg transition-all cursor-pointer"
              >
                Cancelar
              </button>
              <button
                type="submit"
                disabled={loading || !sampleType}
                className="px-4 py-2 text-sm bg-primary text-white rounded-lg hover:bg-primary-light hover:scale-102 transition-all disabled:opacity-70 cursor-pointer"
              >
                {loading ? "Cadastrando..." : "Cadastrar"}
              </button>
            </div>
          </form>
        </div>
      </div>
    </>
  );
}