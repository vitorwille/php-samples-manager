"use client";

import Image from "next/image";
import { useState } from "react";
import { useRouter } from "next/navigation";
import { login, register } from "@/lib/api";
import { Mail, Lock, OctagonAlert, User } from "lucide-react";

export default function LoginPage() {
  const [isLogin, setIsLogin] = useState(true);
  const [name, setName] = useState("");
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);
  const router = useRouter();

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setError("");
    setLoading(true);

    try {
      if (isLogin) {
        const ok = await login(email, password);
        if (ok) {
          router.push("/");
        } else {
          setError("Email ou senha inválidos.");
        }
      } else {
        const result = await register(name, email, password);
        if (result.ok) {
          const loggedIn = await login(email, password);
          if (loggedIn) {
            router.push("/");
          }
        } else {
          if (result.error === "Email already registered.") {
            setError("Este email já está cadastrado.");
          } else if (result.error === "Invalid password. Must contain more than 8 characters and at least a number and letter.") {
            setError("Senha inválida. Deve conter mais de 8 caracteres e pelo menos uma letra e um número.");
          } else if (result.error === "Invalid email address") {
            setError("Email inválido.");
          } else {
            setError(result.error || "Erro ao criar conta.");
          }
        }
      }
    } catch {
      setError("Erro ao conectar com o servidor.");
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className="min-h-screen w-full flex flex-col md:flex-row bg-[#f8f9fa]">
      <div className="relative hidden md:flex md:w-[50%] lg:w-[58%] flex-col justify-between p-8 lg:p-12 overflow-hidden select-none">
        <Image src="/img/login-bg.jpg" alt="Placa de Petri sendo observada em microscópio." fill className="object-cover" quality={100} sizes="120vw" />
        <div className="absolute inset-0 bg-primary/65" />
        <div className="relative z-10 flex items-center gap-3">
          <div className="bg-white p-1 rounded-lg flex items-center justify-center shadow-xs">
            <Image src="/img/microlims-logo.png" alt="MicroLIMS - logo" width={40} height={40} className="shrink-0" />
          </div>
          <div className="flex items-center gap-1.5 text-xs text-white/80">
            <span className="font-bold text-white text-sm">MicroLIMS</span>
            <span className="text-white/60">v1.0 - </span>
            <a href="https://github.com/vitorwille" target="_blank" rel="noopener noreferrer" className="text-white/60 hover:text-white hover:underline cursor-pointer">
              github.com/vitorwille
            </a>
          </div>
        </div>

        <div className="relative z-10 max-w-xl my-auto">
          <h1 className="text-3xl lg:text-5xl font-bold text-white leading-tight mb-3 tracking-tight">
            Controle de amostras<br />simples e eficiente.
          </h1>

          <div className="flex items-center gap-2.5 text-white/90 text-[13px] leading-relaxed max-w-lg">
            <OctagonAlert className="w-6 h-6 text-red-500 shrink-0 mt-0.5" />
            <p>
              Esta aplicação foi desenvolvida como projeto educacional, não se tratando de um produto comercial. Sua utilização profissional não é recomendada ou endossada.
            </p>
          </div>
        </div>
      </div>

      <div className="flex-1 flex items-center justify-center p-6 md:p-10">
        <div className="bg-white rounded-2xl border border-gray-100 shadow-xs p-8 !pb-6 !pt-5 sm:p-10 w-full max-w-[420px]">
          <div className="flex items-start gap-3">
            <Image src="/img/microlims-logo.png" alt="MicroLIMS" width={44} height={44} className="shrink-0" />
            <div>
              <div className="flex items-center gap-1.5 text-xs text-slate-600">
                <span className="font-bold text-slate-800">MicroLIMS</span>
                <span className="text-slate-500">v1.0 - </span>
                <a href="https://github.com/vitorwille" target="_blank" rel="noopener noreferrer" className="text-slate-500 hover:text-slate-800 hover:underline cursor-pointer"
                >
                  github.com/vitorwille
                </a>
              </div>
              <p className="text-[11px] text-slate-500/65 mt-1">
                projeto criado para estudo pessoal.
              </p>
            </div>
          </div>

          <div className="mb-6 mt-3">
            <h2 className="text-xl font-bold text-slate-900 tracking-tight">
              {isLogin ? "Acessar Painel" : "Criar Conta"}
            </h2>
            <p className="text-xs text-gray-500 mt-1">
              {isLogin ? "Faça login para acessar o painel de amostras." : "Preencha abaixo para criar sua conta e acessar o painel."}
            </p>
          </div>

          {error && (
            <div className="bg-red-50 border border-red-100 text-red-700 p-3 rounded-lg text-xs mb-4">
              {error}
            </div>
          )}

          <form onSubmit={handleSubmit} className="space-y-4">
            {!isLogin && (
              <div>
                <label className="text-[11px] font-semibold text-slate-600 tracking-wider uppercase mb-1.5 flex items-center gap-1.5">
                  <User className="w-3.5 h-3.5 text-slate-400" /> NOME
                </label>
                <input
                  type="text"
                  value={name}
                  onChange={(e) => setName(e.target.value)}
                  required
                  className="w-full px-3.5 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-slate-400 focus:ring-1 focus:ring-slate-400 text-slate-800 placeholder:text-gray-400 transition"
                  placeholder="Seu nome"
                />
              </div>
            )}

            <div>
              <label className="text-[11px] font-semibold text-slate-600 tracking-wider uppercase mb-1.5 flex items-center gap-1.5">
                <Mail className="w-3.5 h-3.5 text-slate-400" /> E-MAIL
              </label>
              <input
                type="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                required
                className="w-full px-3.5 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-slate-400 focus:ring-1 focus:ring-slate-400 text-slate-800 placeholder:text-gray-400 transition"
                placeholder="seu.email@exemplo.com"
              />
            </div>

            <div>
              <label className="text-[11px] font-semibold text-slate-600 tracking-wider uppercase mb-1.5 flex items-center gap-1.5">
                <Lock className="w-3.5 h-3.5 text-slate-400" /> SENHA
              </label>
              <input
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                required
                className="w-full px-3.5 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-slate-400 focus:ring-1 focus:ring-slate-400 text-slate-800 placeholder:text-gray-400 transition"
                placeholder="••••••••"
              />
            </div>

            <button
              type="submit"
              disabled={loading}
              className="w-full mt-2 bg-[#1e3a5f] hover:bg-[#152a45] hover:scale-102 text-white font-medium py-2.5 px-4 rounded-lg text-sm transition-all duration-250 disabled:opacity-50 cursor-pointer"
            >
              {loading ? (isLogin ? "Entrando..." : "Criando...") : (isLogin ? "Entrar" : "Criar Conta")}
            </button>
          </form>

          <div className="mt-6 text-center text-xs text-gray-500">
            {isLogin ? "Não possui uma conta?" : "Já possui uma conta?"}{" "}
            <button
              onClick={() => {
                setIsLogin(!isLogin);
                setError("");
                setName("");
                setEmail("");
                setPassword("");
              }}
              className="font-semibold text-slate-800 hover:underline cursor-pointer">
              {isLogin ? "Registre-se" : "Faça login"}
            </button>
          </div>
        </div>
      </div>
    </div >
  );
}

