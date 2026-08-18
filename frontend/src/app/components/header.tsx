"use client";

import { useRouter } from "next/navigation";
import Image from "next/image";
import { logout } from "@/lib/api";
import { DoorOpen } from "lucide-react";

export default function Header() {
  const router = useRouter();

  async function handleLogout() {
    await logout();
    router.push("/login");
  }

  return (
    <header className="bg-primary text-white px-6 py-3 flex items-center justify-between">
      <div className="flex items-center gap-2">
        <div className="bg-white/98 rounded-xl p-1">
          <Image src="/img/microlims-logo.png" alt="MicroLIMS - logo" width={36} height={36} />
        </div>
        <div>
          <span className="font-bold text-lg">MicroLIMS</span>
          <span className="text-xs text-white/60 ml-2">v1.0 - <a href="https://github.com/vitorwille" target="_blank" rel="noopener noreferrer">github.com/vitorwille</a></span>
          <p className="text-xs text-white/50">não representa produto comercial.</p>
        </div>
      </div>
      <button
        onClick={handleLogout}
        className="flex items-center gap-1 bg-white/10 hover:bg-red-800 px-4 py-2 rounded-lg text-sm transition-colors cursor-pointer"
      >
        <DoorOpen className="w-5 h-5" />
        Sair
      </button>
    </header>
  );
}
