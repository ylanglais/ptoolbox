CREATE TABLE ref.country (
    id integer NOT NULL,
    code integer,
    code2 character(2),
    code3 character(3),
    name character varying(100),
    cap_name character varying(100),
    ori_name character varying(100)
);

--
-- Data for Name: country; Type: TABLE DATA; Schema: ref; Owner: ylanglais
--

COPY ref.country (id, code, code2, code3, name, cap_name, ori_name) FROM stdin;
1	4	AF	AFG	Afghanistan	AFGHANISTAN	د افغانستان اسلامي دولتدولت اسلامی افغانستان
2	710	ZA	ZAF	Afrique du Sud	AFRIQUE DU SUD	Republic of South Africa
3	248	AX	ALA	Îles Åland	ÅLAND, ÎLES	Landskapet Åland ; Ahvenanmaan maakunta ; (État libre associé d'Åland)
4	8	AL	ALB	Albanie	ALBANIE	Shqipëri ; Republika e Shqipërisë ; (République d'Albanie)
5	12	DZ	DZA	Algérie	ALGÉRIE	الجمهورية الجزائرية الديمقراطية الشعبية
6	276	DE	DEU	Allemagne	ALLEMAGNE	Bundesrepublik Deutschland
7	20	AD	AND	Andorre	ANDORRE	Principat d'Andorra
8	24	AO	AGO	Angola	ANGOLA	República de Angola
9	660	AI	AIA	Anguilla	ANGUILLA	Anguilla
10	10	AQ	ATA	Antarctique	ANTARCTIQUE	The Antarctic Treaty
11	28	AG	ATG	Antigua-et-Barbuda	ANTIGUA-ET-BARBUDA	Antigua and Barbadua
12	682	SA	SAU	Arabie saoudite	ARABIE SAOUDITE	المملكة العربية السعودية
13	32	AR	ARG	Argentine	ARGENTINE	Argentina
14	51	AM	ARM	Arménie	ARMÉNIE	Հայաստան
15	533	AW	ABW	Aruba	ARUBA	Aruba
16	36	AU	AUS	Australie	AUSTRALIE	Australia
17	40	AT	AUT	Autriche	AUTRICHE	Österreich
18	31	AZ	AZE	Azerbaïdjan	AZERBAÏDJAN	Azərbaycan Respublikası
19	44	BS	BHS	Bahamas	BAHAMAS	Commonwealth of the Bahamas
20	48	BH	BHR	Bahreïn	BAHREÏN	مملكة البحرين
21	50	BD	BGD	Bangladesh	BANGLADESH	গণপ্রজাতন্ত্রী বাংলাদেশ
22	52	BB	BRB	Barbade	BARBADE	Barbados
23	112	BY	BLR	Biélorussie	BÉLARUS	Беларусь (Belarusy)
24	56	BE	BEL	Belgique	BELGIQUE	België
25	84	BZ	BLZ	Belize	BELIZE	Belize
26	204	BJ	BEN	Bénin	BÉNIN	Bénin
27	60	BM	BMU	Bermudes	BERMUDES	Bermuda
28	64	BT	BTN	Bhoutan	BHOUTAN	འབྲུག་ཡུལ
29	68	BO	BOL	Bolivie	BOLIVIE, ÉTAT PLURINATIONAL DE	Estado Plurinacional de Bolivia
30	535	BQ	BES	Pays-Bas caribéens	BONAIRE, SAINT-EUSTACHE ET SABA	Bonaire, Sint-Eustatius, en Saba
31	70	BA	BIH	Bosnie-Herzégovine	BOSNIE-HERZÉGOVINE	Republika Bosna i Hercegovina
32	72	BW	BWA	Botswana	BOTSWANA	Republic of Botswana
33	74	BV	BVT	Île Bouvet	BOUVET, ÎLE	Bouvetøya
34	76	BR	BRA	Brésil	BRÉSIL	República Federativa do Brasil
35	96	BN	BRN	Brunei	BRUNÉI DARUSSALAM	بروني دارالسلام
36	100	BG	BGR	Bulgarie	BULGARIE	Република България
37	854	BF	BFA	Burkina Faso	BURKINA FASO	Burkina Faso
38	108	BI	BDI	Burundi	BURUNDI	République du Burundi
39	136	KY	CYM	Îles Caïmans	CAÏMANES, ÎLES	Cayman Islands
40	116	KH	KHM	Cambodge	CAMBODGE	ព្រះរាជាណាចក្រកម្ពុជា
41	120	CM	CMR	Cameroun	CAMEROUN	Cameroun
42	124	CA	CAN	Canada	CANADA	Canada
43	132	CV	CPV	Cap-Vert	CABO VERDE	Cabo Verde
44	140	CF	CAF	République centrafricaine	CENTRAFRICAINE, RÉPUBLIQUE	Ködörösêse tî Bêafrîka
45	152	CL	CHL	Chili	CHILI	Chile
46	156	CN	CHN	Chine	CHINE	中华人民共和国
47	162	CX	CXR	Île Christmas	CHRISTMAS, ÎLE	Christmas Islands
48	196	CY	CYP	Chypre	CHYPRE	Κύπρος
49	166	CC	CCK	Îles Cocos	COCOS (KEELING), ÎLES	Territory of Cocos Island
50	170	CO	COL	Colombie	COLOMBIE	Colombia
51	174	KM	COM	Comores	COMORES	جزر القُمُر
52	178	CG	COG	République du Congo	CONGO	République du Congo
53	180	CD	COD	République démocratique du Congo	CONGO, RÉPUBLIQUE DÉMOCRATIQUE DU	République démocratique du Congo
54	184	CK	COK	Îles Cook	COOK, ÎLES	Cook Islands
55	410	KR	KOR	Corée du Sud	CORÉE, RÉPUBLIQUE DE	대한민국
56	408	KP	PRK	Corée du Nord	CORÉE, RÉPUBLIQUE POPULAIRE DÉMOCRATIQUE DE	조선민주주의인민공화국
57	188	CR	CRI	Costa Rica	COSTA RICA	República de Costa Rica
58	384	CI	CIV	Côte d'Ivoire	CÔTE D'IVOIRE	Côte d'Ivoire
59	191	HR	HRV	Croatie	CROATIE	Republika Hrvatska
60	192	CU	CUB	Cuba	CUBA	República de Cuba
61	531	CW	CUW	Curaçao	CURAÇAO	Curaçao, Kòrsou
62	208	DK	DNK	Danemark	DANEMARK	Kongeriget Danmark
63	262	DJ	DJI	Djibouti	DJIBOUTI	; جمهورية جيبوتي
64	214	DO	DOM	République dominicaine	DOMINICAINE, RÉPUBLIQUE	República Dominicana
65	212	DM	DMA	Dominique	DOMINIQUE	Commonwealth of Dominica
66	818	EG	EGY	Égypte	ÉGYPTE	جمهوريّة مصر العربيّة,
67	222	SV	SLV	Salvador	EL SALVADOR	República de El Salvador
68	784	AE	ARE	Émirats arabes unis	ÉMIRATS ARABES UNIS	دولة الإمارات العربيّة المتّحدة
69	218	EC	ECU	Équateur	ÉQUATEUR	República del Ecuador
70	232	ER	ERI	Érythrée	ÉRYTHRÉE	ሃገረ ኤርትራ
71	724	ES	ESP	Espagne	ESPAGNE	España
72	233	EE	EST	Estonie	ESTONIE	Eesti
73	840	US	USA	États-Unis	ÉTATS-UNIS	United States of America
74	231	ET	ETH	Éthiopie	ÉTHIOPIE	የኢትዮጵያ ፌዴራላዊ ዲሞክራሲያዊ ሪፐብሊክ
75	238	FK	FLK	Malouines	FALKLAND, ÎLES (MALVINAS)	Falkland Islands
76	234	FO	FRO	Îles Féroé	FÉROÉ, ÎLES	Føroyar ; Færøerne
77	242	FJ	FJI	Fidji	FIDJI	फ़िजी द्वीप समूह गणराज्य
78	246	FI	FIN	Finlande	FINLANDE	Suomen Tasavalta
79	250	FR	FRA	France	FRANCE	République française
80	266	GA	GAB	Gabon	GABON	République gabonaise
81	270	GM	GMB	Gambie	GAMBIE	Republic of Gambia
82	268	GE	GEO	Géorgie	GÉORGIE	საქართველო
83	239	GS	SGS	Géorgie du Sud-et-les îles Sandwich du Sud	GÉORGIE DU SUD ET LES ÎLES SANDWICH DU SUD	Géorgie du Sud-et-les Îles Sandwich du Sud
84	288	GH	GHA	Ghana	GHANA	Republic of Ghana
85	292	GI	GIB	Gibraltar	GIBRALTAR	 
86	300	GR	GRC	Grèce	GRÈCE	Ελλάδα
87	308	GD	GRD	Grenade	GRENADE	Commonwealth of Grenada
88	304	GL	GRL	Groenland	GROENLAND	Grønland
89	312	GP	GLP	Guadeloupe	GUADELOUPE	 
90	316	GU	GUM	Guam	GUAM	Guåhån
91	320	GT	GTM	Guatemala	GUATEMALA	República de Guatemala
92	831	GG	GGY	Guernesey	GUERNESEY	Guernsey
93	324	GN	GIN	Guinée	GUINÉE	République de Guinée
94	624	GW	GNB	Guinée-Bissau	GUINÉE-BISSAU	República da Guiné-Bissau
95	226	GQ	GNQ	Guinée équatoriale	GUINÉE ÉQUATORIALE	República de Guiena ecuatorial
96	328	GY	GUY	Guyana	GUYANA	Co-Operative Republic of Guyana
97	254	GF	GUF	Guyane	GUYANE FRANÇAISE	Guyane
98	332	HT	HTI	Haïti	HAÏTI	Repiblik d'Ayiti ; République d'Haïti
99	334	HM	HMD	Îles Heard-et-MacDonald	HEARD ET MACDONALD, ÎLES	Heard Island and McDonald Islands
100	340	HN	HND	Honduras	HONDURAS	República de Honduras
101	344	HK	HKG	Hong Kong	HONG KONG	香港
102	348	HU	HUN	Hongrie	HONGRIE	Magyar
103	833	IM	IMN	Île de Man	ÎLE DE MAN	Isle of Man
104	581	UM	UMI	  Îles mineures éloignées des États-Unis	ÎLES MINEURES ÉLOIGNÉES DES ÉTATS-UNIS	United States Minor Outlying Islands
105	92	VG	VGB	Îles Vierges britanniques	ÎLES VIERGES BRITANNIQUES	British Virgin Islands
106	850	VI	VIR	Îles Vierges des États-Unis	ÎLES VIERGES DES ÉTATS-UNIS	US Virgin Islands
107	356	IN	IND	Inde	INDE	Republic of India
108	360	ID	IDN	Indonésie	INDONÉSIE	Republik Indonesia
109	364	IR	IRN	Iran	IRAN, RÉPUBLIQUE ISLAMIQUE D'	جمهوری اسلامی ايران
110	368	IQ	IRQ	Irak	IRAQ	العراق
111	372	IE	IRL	Irlande	IRLANDE	Éire
112	352	IS	ISL	Islande	ISLANDE	Ísland
113	376	IL	ISR	Israël	ISRAËL	מְדִינַת יִשְׂרָאֵל
114	380	IT	ITA	Italie	ITALIE	Italia
115	388	JM	JAM	Jamaïque	JAMAÏQUE	Jamaïca
116	392	JP	JPN	Japon	JAPON	日本国
117	832	JE	JEY	Jersey	JERSEY	Bailiwick of Jersey, Bailliage de Jersey
118	400	JO	JOR	Jordanie	JORDANIE	المملكة الأردنّيّة الهاشميّة
119	398	KZ	KAZ	Kazakhstan	KAZAKHSTAN	Қазақстан Республикасы
120	404	KE	KEN	Kenya	KENYA	Jamhuri ya Kenya
121	417	KG	KGZ	Kirghizistan	KIRGHIZISTAN	Кыргыз Республикасы
122	296	KI	KIR	Kiribati	KIRIBATI	Kiribati
123	414	KW	KWT	Koweït	KOWEÏT	دولة الكويت
124	418	LA	LAO	Laos	LAO, RÉPUBLIQUE DÉMOCRATIQUE POPULAIRE	ສາທາລະນະລັດ ປະຊາທິປະໄຕ ປະຊາຊົນລາວ
125	426	LS	LSO	Lesotho	LESOTHO	Muso oa Lesotho
126	428	LV	LVA	Lettonie	LETTONIE	Latvijas
127	422	LB	LBN	Liban	LIBAN	 الجمهوريّةاللبنانيّة
128	430	LR	LBR	Liberia	LIBÉRIA	Republic of Liberia
129	434	LY	LBY	Libye	LIBYE	دولة ليبيا
130	438	LI	LIE	Liechtenstein	LIECHTENSTEIN	Fürstentum Liechtenstein
131	440	LT	LTU	Lituanie	LITUANIE	Lietuvos Respublika
132	442	LU	LUX	Luxembourg	LUXEMBOURG	Groussherzogtum Lëtzebuerg
133	446	MO	MAC	Macao	MACAO	Região Administrativa Especial de Macau da República Popular da China
134	807	MK	MKD	Macédoine du Nord	RÉPUBLIQUE DE MACÉDOINE	Република Македонија
135	450	MG	MDG	Madagascar	MADAGASCAR	République de Madagascar ; Repoblikan'i Madagasikara
136	458	MY	MYS	Malaisie	MALAISIE	Malaysia
137	454	MW	MWI	Malawi	MALAWI	Dziko la Malaŵi
138	462	MV	MDV	Maldives	MALDIVES	ދިވެހިރާއްޖޭގެ ޖުމްހޫރިއްޔާ
139	466	ML	MLI	Mali	MALI	République du Mali
140	470	MT	MLT	Malte	MALTE	Repubblika ta' Malta
141	580	MP	MNP	Îles Mariannes du Nord	MARIANNES DU NORD, ÎLES	Commonwealth of the Northern Mariana Islands
142	504	MA	MAR	Maroc	MAROC	المملكة المغربية
143	584	MH	MHL	Îles Marshall	MARSHALL, ÎLES	Aolepān Aorōkin M̧ajeļ
144	474	MQ	MTQ	Martinique	MARTINIQUE	 
145	480	MU	MUS	Maurice	MAURICE	Mauritius
146	478	MR	MRT	Mauritanie	MAURITANIE	الجمهورية الإسلامية الموريتانية
147	175	YT	MYT	Mayotte	MAYOTTE	Mayotte
148	484	MX	MEX	Mexique	MEXIQUE	Estados Unidos Mexicanos
149	583	FM	FSM	États fédérés de Micronésie	MICRONÉSIE, ÉTATS FÉDÉRÉS DE	Federated States of Micronesia
150	498	MD	MDA	Moldavie	MOLDAVIE	Republica Moldova
151	492	MC	MCO	Monaco	MONACO	 
152	496	MN	MNG	Mongolie	MONGOLIE	Монгол Улс
153	499	ME	MNE	Monténégro	MONTÉNÉGRO	Црна Гора
154	500	MS	MSR	Montserrat	MONTSERRAT	Montserrat
155	508	MZ	MOZ	Mozambique	MOZAMBIQUE	República de Moçambique
156	104	MM	MMR	Birmanie	MYANMAR	Union of Myanmar
157	516	NA	NAM	Namibie	NAMIBIE	Namibia
158	520	NR	NRU	Nauru	NAURU	Ripublik Naoero
159	524	NP	NPL	Népal	NÉPAL	
160	558	NI	NIC	Nicaragua	NICARAGUA	Republica de Nicaragua
161	562	NE	NER	Niger	NIGER	 
162	566	NG	NGA	Nigeria	NIGÉRIA	Nigeria
163	570	NU	NIU	Niue	NIUÉ	Niue
164	574	NF	NFK	Île Norfolk	NORFOLK, ÎLE	Norfolk Island
165	578	NO	NOR	Norvège	NORVÈGE	Kongeriket Norge
166	540	NC	NCL	Nouvelle-Calédonie	NOUVELLE-CALÉDONIE	 
167	554	NZ	NZL	Nouvelle-Zélande	NOUVELLE-ZÉLANDE	New Zealand
168	86	IO	IOT	Territoire britannique de l'océan Indien	OCÉAN INDIEN, TERRITOIRE BRITANNIQUE DE L'	British Indian Ocean Territory
169	512	OM	OMN	Oman	OMAN	سلطنة عُمان
170	800	UG	UGA	Ouganda	OUGANDA	Jamhuri ya Uganda
171	860	UZ	UZB	Ouzbékistan	OUZBÉKISTAN	O'zbekiston Respublikasi
172	586	PK	PAK	Pakistan	PAKISTAN	اسلامی جمہوریت پاکستان
173	585	PW	PLW	Palaos	PALAOS	Beluu er a Belau
174	275	PS	PSE	Palestine	ÉTAT DE PALESTINE	دولة فلسطين
175	591	PA	PAN	Panama	PANAMA	República de Panamá
176	598	PG	PNG	Papouasie-Nouvelle-Guinée	PAPOUASIE-NOUVELLE-GUINÉE	l'État indépendant de Papouasie-Nouvelle-Guinée
177	600	PY	PRY	Paraguay	PARAGUAY	República del Paraguay
178	528	NL	NLD	Pays-Bas	PAYS-BAS	Nederland
179	604	PE	PER	Pérou	PÉROU	Perú
180	608	PH	PHL	Philippines	PHILIPPINES	Republika ng Pilipinas
181	612	PN	PCN	Îles Pitcairn	PITCAIRN	Pitcairn
182	616	PL	POL	Pologne	POLOGNE	Polska
183	258	PF	PYF	Polynésie française	POLYNÉSIE FRANÇAISE	Polynésie française
184	630	PR	PRI	Porto Rico	PORTO RICO	Estado Libre Asociado de Puerto Rico 
185	620	PT	PRT	Portugal	PORTUGAL	Portugal
186	634	QA	QAT	Qatar	QATAR	دولة قطر
187	638	RE	REU	La Réunion	RÉUNION	La Réunion
188	642	RO	ROU	Roumanie	ROUMANIE	România
189	826	GB	GBR	Royaume-Uni	ROYAUME-UNI	United Kingdom
190	643	RU	RUS	Russie	RUSSIE, FÉDÉRATION DE	Российская Федерация
191	646	RW	RWA	Rwanda	RWANDA	Repubulika y'u Rwanda
192	732	EH	ESH	République arabe sahraouie démocratique	SAHARA OCCIDENTAL	Western Sahara
193	652	BL	BLM	Saint-Barthélemy	SAINT-BARTHÉLEMY	 
194	659	KN	KNA	Saint-Christophe-et-Niévès	SAINT-KITTS-ET-NEVIS	Saint Kitts and Nevis
195	674	SM	SMR	Saint-Marin	SAINT-MARIN	San Marino
196	663	MF	MAF	Saint-Martin	SAINT-MARTIN (PARTIE FRANÇAISE)	 
197	534	SX	SXM	Saint-Martin	SAINT-MARTIN (PARTIE NÉERLANDAISE)	Sint Maarten
198	666	PM	SPM	Saint-Pierre-et-Miquelon	SAINT-PIERRE-ET-MIQUELON	 
199	336	VA	VAT	Saint-Siège (État de la Cité du Vatican)	SAINT-SIÈGE (ÉTAT DE LA CITÉ DU VATICAN)	Stato della Città del Vaticano
200	670	VC	VCT	Saint-Vincent-et-les-Grenadines	SAINT-VINCENT-ET-LES-GRENADINES	Saint Vincent and the Grenadines
201	654	SH	SHN	Sainte-Hélène, Ascension et Tristan da Cunha	SAINTE-HÉLÈNE, ASCENSION ET TRISTAN DA CUNHA	Saint Helena, Assunsion and Tristan da Cunha
202	662	LC	LCA	Sainte-Lucie	SAINTE-LUCIE	Commonwealth of Saint Lucia
203	90	SB	SLB	Îles Salomon	SALOMON, ÎLES	Solomons Islands
204	882	WS	WSM	Samoa	SAMOA	Malo Sa'oloto Tuto'atasi o Samoa
205	16	AS	ASM	Samoa américaines	SAMOA AMÉRICAINES	American Samoa
206	678	ST	STP	Sao Tomé-et-Principe	SAO TOMÉ-ET-PRINCIPE	República Democrática de São Tomé e Príncipe
207	686	SN	SEN	Sénégal	SÉNÉGAL	République du Sénégal
208	688	RS	SRB	Serbie	SERBIE	Република Србија
209	690	SC	SYC	Seychelles	SEYCHELLES	Repiblik Sesel
210	694	SL	SLE	Sierra Leone	SIERRA LEONE	Sierra Leone
211	702	SG	SGP	Singapour	SINGAPOUR	Republic of Singapore
212	703	SK	SVK	Slovaquie	SLOVAQUIE	Slovenská republika
213	705	SI	SVN	Slovénie	SLOVÉNIE	Republika Slovenija
214	706	SO	SOM	Somalie	SOMALIE	 جمهورية الصومال الفدرالية
215	729	SD	SDN	Soudan	SOUDAN	جمهورية السودان (Jumhuriyat al-Sudan)
216	728	SS	SSD	Soudan du Sud	SOUDAN DU SUD	Republic of South Sudan
217	144	LK	LKA	Sri Lanka	SRI LANKA	Prajatantrika Samajavadi Janarajaya ; Ilankai Sananayaka Sosolisa Kudiyarasu
218	752	SE	SWE	Suède	SUÈDE	Sverige
219	756	CH	CHE	Suisse	SUISSE	Confœderatio Helvetica
220	740	SR	SUR	Suriname	SURINAME	Republiek Suriname
221	744	SJ	SJM	Svalbard et ile Jan Mayen	SVALBARD ET ÎLE JAN MAYEN	Svalbard og Jan Mayen
222	748	SZ	SWZ	Eswatini	ESWATINI	Umbuso we Swatini
223	760	SY	SYR	Syrie	SYRIENNE, RÉPUBLIQUE ARABE	الجمهوريّة العربيّة السّوريّة
224	762	TJ	TJK	Tadjikistan	TADJIKISTAN	Ҷумҳурии Тоҷикистон
225	158	TW	TWN	Taïwan	TAÏWAN	台灣
226	834	TZ	TZA	Tanzanie	TANZANIE, RÉPUBLIQUE UNIE DE	United Republic of Tanzania
227	148	TD	TCD	Tchad	TCHAD	جمهورية تشاد
228	203	CZ	CZE	Tchéquie	TCHÉQUIE	Česká republika
229	260	TF	ATF	Terres australes et antarctiques françaises	TERRES AUSTRALES FRANÇAISES	Terres australes et antarctiques françaises
230	764	TH	THA	Thaïlande	THAÏLANDE	ราชอาณาจักรไทย
231	626	TL	TLS	Timor oriental	TIMOR-LESTE	Repúblika Demokrátika Timor Lorosa'e
232	768	TG	TGO	Togo	TOGO	République togolaise
233	772	TK	TKL	Tokelau	TOKELAU	Tokelau
234	776	TO	TON	Tonga	TONGA	Pule'anga Fakatu'i 'o Tonga
235	780	TT	TTO	Trinité-et-Tobago	TRINITÉ-ET-TOBAGO	Republic of Trinidad and Tobago
236	788	TN	TUN	Tunisie	TUNISIE	الجمهورية التونسية
237	795	TM	TKM	Turkménistan	TURKMÉNISTAN	Türkmenistan Respublikasy
238	796	TC	TCA	Îles Turques-et-Caïques	TURKS ET CAÏQUES, ÎLES	Turks-and-Caicos
239	792	TR	TUR	Turquie	TURQUIE	Türkiye Cumhuriyeti
240	798	TV	TUV	Tuvalu	TUVALU	Tuvalu
241	804	UA	UKR	Ukraine	UKRAINE	Украïна
242	858	UY	URY	Uruguay	URUGUAY	República Oriental del Uruguay
243	548	VU	VUT	Vanuatu	VANUATU	Ripablik blong Vanuatu
244	862	VE	VEN	Venezuela	VENEZUELA, RÉPUBLIQUE BOLIVARIENNE DU	República Bolivariana de Venezuela
245	704	VN	VNM	Viêt Nam	VIET NAM	Cộng Hoà Xã Hội Chủ Nghĩa Việt Nam
246	876	WF	WLF	Wallis-et-Futuna	WALLIS-ET-FUTUNA	Wallis-et-Futuna
247	887	YE	YEM	Yémen	YÉMEN	ﺍﻟﺠﻤﻬﻮﺭﯾّﺔ اليمنية
248	894	ZM	ZMB	Zambie	ZAMBIE	Republic of Zambia
249	716	ZW	ZWE	Zimbabwe	ZIMBABWE	Republic of Zimbabwe
\.

insert into db.changelog (action) values ('patch 20230718.countries.sql');
