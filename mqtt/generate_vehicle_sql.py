TOTAL_VEHICLES = 500

with open("carbon_credits_500.sql","w",encoding="utf8") as f:

    # mulai dari 002
    for i in range(2, TOTAL_VEHICLES + 2):

        # device id
        device_id = f"{i:03}"

        # data dummy
        kk = f"111111111111{i:04}"

        nik = f"222222222222{i:04}"

        nrkb = f"B{i:04}TEST"

        # nomor rangka 5 digit
        nomor_rangka = f"{i:05}"

        # 250 motorcycle + 250 car
        if i <= 251:
            vehicle_type = "motorcycle"
        else:
            vehicle_type = "car"

        sql = f"""
INSERT INTO carbon_credits
(
owner_id,
nomor_kartu_keluarga,
pemilik_kendaraan,
nik_e_ktp,
nrkb,
nomor_rangka_5digit,
vehicle_type,
device_id,
status,
sensor_status,
created_at,
updated_at
)
VALUES
(
2,
'{kk}',
'Milik Sendiri',
'{nik}',
'{nrkb}',
'{nomor_rangka}',
'{vehicle_type}',
'{device_id}',
'approved',
'active',
NOW(),
NOW()
);
"""

        f.write(sql)

print("DONE → carbon_credits_500.sql")