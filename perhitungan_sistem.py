# ============================================================
# PERHITUNGAN CO2e SISTEM
# Disesuaikan dengan perhitungan manual pada skripsi
# ============================================================

# -----------------------------
# Data input dari sensor
# -----------------------------
co_ppm = 31.16        # ppm
Q = 18.24             # L/min
v = 1.96              # km/jam

# -----------------------------
# Parameter perhitungan
# -----------------------------
Vm = 22.88            # L/mol
kh = 1                # faktor koreksi
MW_CO = 28.01         # g/mol
GWP_CO = 1.571        # faktor GWP CO

# -----------------------------
# Perhitungan faktor konversi massa
# MC = (Q × 60 × MW) / (v × Vm × 1.000.000) × kh
# -----------------------------
MC_CO = (Q * 60 * MW_CO) / (v * Vm * 1_000_000) * kh

# -----------------------------
# Perhitungan emisi CO dalam g/km
# CO g/km = CO ppm × MC
# -----------------------------
co_gkm = co_ppm * MC_CO

# -----------------------------
# Perhitungan CO2e
# CO2e = CO g/km × GWP CO
# -----------------------------
co2e_total = co_gkm * GWP_CO

# -----------------------------
# Output hasil sistem
# -----------------------------
print("========== HASIL PERHITUNGAN SISTEM ==========")
print(f"CO ppm        : {co_ppm}")
print(f"Q             : {Q} L/min")
print(f"v             : {v} km/jam")
print(f"Vm            : {Vm} L/mol")
print(f"MW CO         : {MW_CO} g/mol")
print(f"kh            : {kh}")
print(f"GWP CO        : {GWP_CO}")
print()
print(f"MC CO         : {MC_CO:.10f}")
print(f"CO g/km       : {co_gkm:.5f}")
print(f"CO2e g/km     : {co2e_total:.5f}")