#!/usr/bin/env python3
"""
Generate demo SVG assets and fixture data for the storefront.

Run once: `python3 bin/generate-demo-assets.py`. Produces:
  - public/img/products/<id>.svg          (per-product illustration)
  - public/img/brands/<slug>.svg          (per-brand wordmark)
  - public/img/banners/hero.svg           (homepage hero banner)
  - storage/data/reviews.json             (1-2 reviews per product)

The illustrations are intentionally stylised — not photorealistic, not
real brand logos — so the demo can ship cleanly without licensing issues.
"""

import json
import os
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent

# ─── product illustrations ────────────────────────────────────────────────────


def smartphone(color: str, screen: str = "#1a1a2e") -> str:
    return f"""<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200">
  <rect width="200" height="200" fill="#fafafa"/>
  <rect x="68" y="22" width="64" height="156" rx="10" fill="{color}"/>
  <rect x="74" y="36" width="52" height="120" rx="3" fill="{screen}"/>
  <circle cx="100" cy="168" r="3" fill="#444"/>
  <rect x="92" y="28" width="16" height="2" rx="1" fill="#444"/>
</svg>"""


def laptop(lid: str, screen: str = "#1a1a2e") -> str:
    return f"""<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200">
  <rect width="200" height="200" fill="#fafafa"/>
  <rect x="38" y="48" width="124" height="80" rx="4" fill="{lid}"/>
  <rect x="44" y="54" width="112" height="68" rx="2" fill="{screen}"/>
  <path d="M28 132 L172 132 L182 156 L18 156 Z" fill="{lid}"/>
  <rect x="86" y="132" width="28" height="3" rx="1" fill="#666"/>
</svg>"""


def tv(bezel: str, screen: str = "#0b1422") -> str:
    return f"""<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200">
  <rect width="200" height="200" fill="#fafafa"/>
  <rect x="20" y="40" width="160" height="100" rx="4" fill="{bezel}"/>
  <rect x="26" y="46" width="148" height="88" rx="2" fill="{screen}"/>
  <path d="M88 140 L88 160 L60 168 L140 168 L112 160 L112 140 Z" fill="{bezel}"/>
</svg>"""


def headphones(color: str) -> str:
    return f"""<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200">
  <rect width="200" height="200" fill="#fafafa"/>
  <path d="M50 110 Q50 50 100 50 Q150 50 150 110" fill="none" stroke="{color}" stroke-width="6" stroke-linecap="round"/>
  <ellipse cx="50" cy="125" rx="20" ry="28" fill="{color}"/>
  <ellipse cx="150" cy="125" rx="20" ry="28" fill="{color}"/>
  <ellipse cx="52" cy="125" rx="11" ry="18" fill="#222"/>
  <ellipse cx="148" cy="125" rx="11" ry="18" fill="#222"/>
</svg>"""


def earbuds(color: str) -> str:
    return f"""<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200">
  <rect width="200" height="200" fill="#fafafa"/>
  <ellipse cx="70" cy="92" rx="14" ry="18" fill="{color}"/>
  <rect x="62" y="98" width="16" height="48" rx="6" fill="{color}"/>
  <ellipse cx="130" cy="92" rx="14" ry="18" fill="{color}"/>
  <rect x="122" y="98" width="16" height="48" rx="6" fill="{color}"/>
</svg>"""


def fridge(body: str) -> str:
    return f"""<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200">
  <rect width="200" height="200" fill="#fafafa"/>
  <rect x="60" y="22" width="80" height="160" rx="6" fill="{body}"/>
  <line x1="60" y1="78" x2="140" y2="78" stroke="#bbb" stroke-width="2"/>
  <rect x="64" y="40" width="3" height="22" rx="1" fill="#bbb"/>
  <rect x="64" y="92" width="3" height="44" rx="1" fill="#bbb"/>
</svg>"""


def coffee(body: str) -> str:
    return f"""<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200">
  <rect width="200" height="200" fill="#fafafa"/>
  <rect x="50" y="40" width="100" height="100" rx="10" fill="{body}"/>
  <rect x="86" y="78" width="28" height="22" fill="#222"/>
  <rect x="92" y="100" width="16" height="14" fill="#444"/>
  <rect x="62" y="146" width="76" height="20" rx="3" fill="#888"/>
  <ellipse cx="100" cy="148" rx="14" ry="3" fill="#3b1f0c"/>
</svg>"""


def vacuum_stick(color: str) -> str:
    return f"""<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200">
  <rect width="200" height="200" fill="#fafafa"/>
  <rect x="124" y="40" width="32" height="48" rx="6" fill="{color}"/>
  <rect x="100" y="84" width="14" height="24" rx="3" fill="#444"/>
  <path d="M114 40 Q70 80 90 130" fill="none" stroke="{color}" stroke-width="10" stroke-linecap="round"/>
  <rect x="56" y="142" width="80" height="20" rx="4" fill="{color}"/>
</svg>"""


def vacuum_robot(color: str) -> str:
    return f"""<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200">
  <rect width="200" height="200" fill="#fafafa"/>
  <ellipse cx="100" cy="115" rx="68" ry="22" fill="#ddd"/>
  <ellipse cx="100" cy="100" rx="68" ry="22" fill="{color}"/>
  <circle cx="100" cy="92" r="10" fill="#222"/>
  <circle cx="100" cy="92" r="4" fill="#888"/>
</svg>"""


def vacuum_vertical(color: str) -> str:
    return f"""<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200">
  <rect width="200" height="200" fill="#fafafa"/>
  <rect x="86" y="20" width="28" height="120" rx="6" fill="{color}"/>
  <rect x="74" y="140" width="52" height="36" rx="4" fill="{color}"/>
  <rect x="64" y="164" width="72" height="16" rx="3" fill="#aaa"/>
</svg>"""


def washer(body: str) -> str:
    return f"""<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200">
  <rect width="200" height="200" fill="#fafafa"/>
  <rect x="40" y="30" width="120" height="140" rx="6" fill="{body}"/>
  <circle cx="100" cy="106" r="44" fill="#e6e6e6"/>
  <circle cx="100" cy="106" r="34" fill="#1a1a2e"/>
  <rect x="56" y="42" width="88" height="14" rx="2" fill="#fff"/>
  <circle cx="142" cy="49" r="3" fill="#666"/>
</svg>"""


PRODUCT_SVGS: dict[int, str] = {
    1001: smartphone("#222", "#0a0a14"),       # iPhone 15
    1002: smartphone("#5b69cc", "#0a0a14"),     # Galaxy S24
    1003: smartphone("#1f3a93"),                # Redmi Note 13 Pro
    1004: smartphone("#e0e0e0", "#1a1a2e"),     # Xiaomi 14 (white)
    2001: laptop("#d4d4d4"),                    # MacBook Air
    2002: laptop("#3b3b3b"),                    # VAIO SX14
    2003: laptop("#5b69cc"),                    # Mi Notebook
    3001: tv("#0a0a14"),                        # LG OLED55C3
    3002: tv("#1a1a2e"),                        # Samsung QLED 65
    3003: tv("#222"),                           # Sony Bravia
    4001: earbuds("#fafafa"),                   # AirPods Pro 2
    4002: headphones("#222"),                   # Sony WH-1000XM5
    4003: headphones("#a4824a"),                # Philips Fidelio
    5001: fridge("#dadada"),                    # Bosch
    5002: fridge("#f0f0f0"),                    # LG
    5003: fridge("#bdbdbd"),                    # Samsung
    5101: coffee("#1a1a2e"),                    # DeLonghi Magnifica
    5102: coffee("#5b3b1c"),                    # Philips LatteGo
    5103: coffee("#3b3b3b"),                    # DeLonghi Dedica
    6001: vacuum_stick("#a437d2"),              # Dyson V15
    6002: vacuum_robot("#222"),                 # Mi Robot X10+
    6003: vacuum_vertical("#bf3434"),           # Tefal X-Force
    6101: washer("#dadada"),                    # Bosch washer
    6102: washer("#f0f0f0"),                    # LG washer
}


# ─── brand wordmarks ──────────────────────────────────────────────────────────


BRAND_WORDMARKS: list[dict[str, str]] = [
    {"slug": "apple",    "label": "Apple",    "color": "#1d1d1f", "weight": "600", "italic": "normal"},
    {"slug": "samsung",  "label": "SAMSUNG",  "color": "#1428a0", "weight": "700", "italic": "normal"},
    {"slug": "lg",       "label": "LG",       "color": "#a50034", "weight": "700", "italic": "normal"},
    {"slug": "sony",     "label": "SONY",     "color": "#000000", "weight": "700", "italic": "normal"},
    {"slug": "bosch",    "label": "BOSCH",    "color": "#dc0028", "weight": "700", "italic": "normal"},
    {"slug": "dyson",    "label": "Dyson",    "color": "#222222", "weight": "700", "italic": "italic"},
    {"slug": "philips",  "label": "PHILIPS",  "color": "#0067b1", "weight": "600", "italic": "normal"},
    {"slug": "xiaomi",   "label": "Xiaomi",   "color": "#ff6700", "weight": "700", "italic": "normal"},
    {"slug": "delonghi", "label": "De'Longhi","color": "#1f3a93", "weight": "700", "italic": "italic"},
    {"slug": "tefal",    "label": "TEFAL",    "color": "#cc0000", "weight": "600", "italic": "normal"},
]


def brand_svg(label: str, color: str, weight: str, italic: str) -> str:
    return f"""<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 80">
  <rect width="240" height="80" fill="#ffffff"/>
  <text x="120" y="50" text-anchor="middle"
        font-family="-apple-system, 'Helvetica Neue', Arial, sans-serif"
        font-size="32" font-weight="{weight}" font-style="{italic}"
        fill="{color}" letter-spacing="1">{label}</text>
</svg>"""


# ─── hero banner ──────────────────────────────────────────────────────────────


HERO_SVG = """<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1600 380" preserveAspectRatio="xMidYMid slice">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="#fff7ed"/>
      <stop offset="100%" stop-color="#ffe4c4"/>
    </linearGradient>
    <linearGradient id="orb" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0%" stop-color="#ff8c42"/>
      <stop offset="100%" stop-color="#e57000"/>
    </linearGradient>
  </defs>
  <rect width="1600" height="380" fill="url(#bg)"/>
  <circle cx="1320" cy="200" r="220" fill="url(#orb)" opacity="0.85"/>
  <circle cx="1100" cy="80" r="60" fill="#ffd9b3" opacity="0.6"/>
  <circle cx="1500" cy="340" r="40" fill="#ffd9b3" opacity="0.7"/>

  <!-- product silhouettes -->
  <g transform="translate(1200,80)" opacity="0.95">
    <rect x="0" y="0" width="84" height="200" rx="14" fill="#1a1a2e"/>
    <rect x="6" y="14" width="72" height="160" rx="6" fill="#ffffff"/>
    <circle cx="42" cy="186" r="3" fill="#444"/>
  </g>
  <g transform="translate(1330,140)" opacity="0.95">
    <rect x="0" y="0" width="180" height="120" rx="6" fill="#0a0a14"/>
    <rect x="8" y="8" width="164" height="100" rx="3" fill="#1d3b66"/>
  </g>

  <!-- copy -->
  <text x="80" y="140" font-family="-apple-system, 'Helvetica Neue', Arial, sans-serif"
        font-size="64" font-weight="800" fill="#1a1a2e">Homy demo store</text>
  <text x="80" y="190" font-family="-apple-system, 'Helvetica Neue', Arial, sans-serif"
        font-size="24" font-weight="500" fill="#5a4534">Бытовая техника и электроника. Портфолио-демо на PHP 8 + JSON.</text>
  <text x="80" y="240" font-family="-apple-system, 'Helvetica Neue', Arial, sans-serif"
        font-size="16" fill="#5a4534">Все цены и наличие — синтетические данные.</text>
  <rect x="80" y="270" width="200" height="50" rx="4" fill="#e57000"/>
  <text x="180" y="302" text-anchor="middle"
        font-family="-apple-system, 'Helvetica Neue', Arial, sans-serif"
        font-size="18" font-weight="600" fill="#ffffff">Открыть каталог →</text>
</svg>
"""


# ─── reviews ──────────────────────────────────────────────────────────────────


# Three review slots per product. The first review is positive, the second
# slightly more measured, the third often missing — that mirrors real review
# distributions and avoids feeling like canned data.
PRODUCT_REVIEWS: dict[int, list[dict]] = {
    1001: [
        ("Алексей",  5, "Отличный смартфон. Камера снимает шикарно, автономности хватает на день уверенной работы.",
         "Камера, автономность, экран", "Цена", 1714560000),
        ("Мария",    4, "В целом нравится, но переход с Android занял время.",
         "Сборка, оптика", "Привыкание к iOS", 1716105600),
        ("Полина",   5, "Экран — главный плюс, после старого OLED всё кажется блёклым.",
         "Дисплей", None, 1719446400),
    ],
    1002: [
        ("Денис",    5, "Galaxy S24 — флагман без компромиссов. AI-функции на самом деле полезные.",
         "Скорость, экран 120 Гц", None, 1716969600),
        ("Светлана", 4, "Камера хороша, но фронталка чуть переусиливает кожу.",
         "Память, экран", "Фронтальная камера", 1718611200),
    ],
    1003: [
        ("Иван",     5, "200 Мп — это маркетинг, но фотки честно очень детальные.",
         "Цена, камера, экран", None, 1715491200),
        ("Олег",     4, "За свои деньги — топ. AMOLED 120 Гц на бюджетке.",
         "Экран, батарея", "MIUI", 1717459200),
    ],
    1004: [
        ("Татьяна",  5, "Компактный фланман. Лежит в руке как родной.",
         "Размер, камера Leica", "Греется в играх", 1718064000),
    ],
    2001: [
        ("Сергей",   5, "MacBook Air M3 — идеальный рабочий ноутбук: тихий, лёгкий, шустрый.",
         "Тишина, вес, экран", None, 1717977600),
        ("Анна",     5, "Купила для дизайна, не пожалела ни секунды. Цвет экрана — космос.",
         "Экран, батарея", None, 1719273600),
    ],
    2002: [
        ("Михаил",   4, "Сборка отличная, но цена кусается. Процессор уже не топ.",
         "Корпус, экран 4K", "Цена, процессор", 1716364800),
    ],
    2003: [
        ("Артём",    4, "Отличное сочетание цены и характеристик. Игры идут на ультре.",
         "RTX 4050, экран", "Шумит вентилятор", 1717545600),
        ("Юлия",     5, "Беру с собой в командировки. Тонкий, аккумулятор держит.",
         "Лёгкий, быстрый", None, 1718841600),
    ],
    3001: [
        ("Иван",     5, "OLED-картинка просто космос. После LCD возвращаться невозможно.",
         "Чёрный цвет, контраст", "Бликует на солнце", 1715923200),
        ("Виктор",   5, "Идеален для кино в тёмной комнате. Dolby Vision работает как надо.",
         "HDR, цвета, звук", None, 1718726400),
    ],
    3002: [
        ("Игорь",    4, "65\" — ровно то, что нужно. Цвета сочные, но чёрный не как на OLED.",
         "Размер, цвета", "Чёрный мог быть глубже", 1716796800),
        ("Елена",    5, "Smart TV работает быстро, поддержка Алисы — приятный бонус.",
         "Smart TV, картинка", None, 1718323200),
    ],
    3003: [
        ("Павел",    5, "Sony Bravia не подвела. Цвета калиброваны прямо из коробки.",
         "Калибровка, Google TV", None, 1717977600),
    ],
    4001: [
        ("Анастасия",4, "Шумоподавление работает, но не как у больших Sony. Зато удобные.",
         "Удобные, Spatial Audio", "ANC слабее WH-1000", 1716105600),
        ("Никита",   5, "После проводных Apple — небо и земля. Подключение мгновенное.",
         "Магия экосистемы", "Цена", 1719446400),
    ],
    4002: [
        ("Полина",   5, "Шумоподавление — лучшее, что я слышала. Беру в самолёт всегда.",
         "ANC, посадка, звук", None, 1718841600),
        ("Максим",   5, "Звук у Sony стал нейтральнее, чем у XM4. Мне нравится.",
         "Звук, ANC, автономность", None, 1719964800),
    ],
    4003: [
        ("Дмитрий",  4, "Hi-Res звучит, но гарнитура подходит для дома, не для улицы.",
         "Звук, материалы", "ANC слабовато", 1717113600),
    ],
    5001: [
        ("Ольга",    5, "Холодильник работает тихо, NoFrost — чудо. Не нужно размораживать.",
         "Тихий, NoFrost", None, 1716105600),
        ("Андрей",   4, "Объём средний. Для семьи из 4 — впритык.",
         "Сборка, тихий", "Объём", 1718323200),
    ],
    5002: [
        ("Ксения",   5, "LG тише прежнего Indesit раз в десять.",
         "Тихий, объём, дизайн", None, 1717372800),
    ],
    5003: [
        ("Илья",     4, "Twin Cooling реально помогает: продукты пахнут как должны.",
         "Twin Cooling, объём", "Громкий вентилятор", 1716624000),
    ],
    5101: [
        ("Алексей",  5, "Кофе вкуснее, чем в кофейне. Зерно мелит сама.",
         "Простая, тихая, кофе вкусный", None, 1717977600),
        ("Юлия",     4, "Чистка чуть утомляет, но к этому привыкаешь.",
         "Кофе, простота", "Уход", 1719446400),
    ],
    5102: [
        ("Светлана", 5, "LatteGo — гениальная штука. Капучино за 30 секунд.",
         "LatteGo, скорость", None, 1718064000),
    ],
    5103: [
        ("Михаил",   4, "Для рожковой — отличная цена. Но ждать прогрев приходится.",
         "Цена, эспрессо", "Прогрев", 1717459200),
        ("Олег",     5, "Молочный капучинатор работает шикарно. Беру вторую.",
         "Капучинатор", "Бак маленький", 1719100800),
    ],
    6001: [
        ("Дмитрий",  5, "Dyson V15 переехал нам кота — смешно, но в реальной жизни пылесос вычищает квартиру за 15 минут.",
         "Мощность, лазер для пыли", "Цена", 1719446400),
        ("Анна",     5, "Лазер показывает столько пыли, что это было неловко.",
         "Лазер, мощность", "Шумный", 1720224000),
    ],
    6002: [
        ("Виктор",   4, "Карта строится быстро, под кроватью находит крошки.",
         "Лидар, автозарядка", "Тряпка слабая", 1717545600),
        ("Татьяна",  5, "Робот стал частью семьи. Имя ему Васька.",
         "Тихий, умный", None, 1719100800),
    ],
    6003: [
        ("Андрей",   4, "Лёгкий, до дальних углов достаёт. Батареи хватает.",
         "Вес, гибкая трубка", "Контейнер маленький", 1717804800),
    ],
    6101: [
        ("Ольга",    5, "10 кг — это очень много. Стираю всё семейное за один заход.",
         "Тихая, экономная", None, 1718064000),
    ],
    6102: [
        ("Илья",     5, "AI DD реально определяет тип ткани. Рубашки не садятся.",
         "AI DD, тихая, прямой привод", None, 1718611200),
        ("Полина",   4, "Стирает отлично, но цикл длинный.",
         "Качество стирки", "Длинные программы", 1719446400),
    ],
}


# ─── runner ───────────────────────────────────────────────────────────────────


def write(path: Path, content: str) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    path.write_text(content, encoding="utf-8")
    print(f"  wrote {path.relative_to(ROOT)}")


def main() -> None:
    print("Generating product images…")
    for pid, svg in PRODUCT_SVGS.items():
        write(ROOT / "public" / "img" / "products" / f"{pid}.svg", svg)

    print("Generating brand wordmarks…")
    for brand in BRAND_WORDMARKS:
        svg = brand_svg(brand["label"], brand["color"], brand["weight"], brand["italic"])
        write(ROOT / "public" / "img" / "brands" / f"{brand['slug']}.svg", svg)

    print("Generating hero banner…")
    write(ROOT / "public" / "img" / "banners" / "hero.svg", HERO_SVG)

    print("Updating products.json with per-product photos…")
    products_path = ROOT / "storage" / "data" / "products.json"
    products = json.loads(products_path.read_text(encoding="utf-8"))
    for product in products:
        product["photos"] = [f"/img/products/{product['id']}.svg"]
    products_path.write_text(
        json.dumps(products, ensure_ascii=False, indent=4) + "\n",
        encoding="utf-8",
    )
    print(f"  wrote {products_path.relative_to(ROOT)}")

    print("Generating reviews.json…")
    reviews_payload: list[dict] = []
    for pid, rows in PRODUCT_REVIEWS.items():
        for author, grade, comment, pros, cons, ts in rows:
            reviews_payload.append({
                "product_id": pid,
                "author": author,
                "grade": grade,
                "comment": comment,
                "pros": pros,
                "cons": cons,
                "created_at": ts,
            })
    reviews_path = ROOT / "storage" / "data" / "reviews.json"
    reviews_path.write_text(
        json.dumps(reviews_payload, ensure_ascii=False, indent=4) + "\n",
        encoding="utf-8",
    )
    print(f"  wrote {reviews_path.relative_to(ROOT)}")

    print("\nDone.")


if __name__ == "__main__":
    main()
