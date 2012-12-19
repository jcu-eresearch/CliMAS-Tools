--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = public, pg_catalog;

ALTER TABLE ONLY public.times DROP CONSTRAINT times_pkey;
ALTER TABLE ONLY public.scenarios DROP CONSTRAINT scenarios_pkey;
ALTER TABLE ONLY public.models DROP CONSTRAINT models_pkey;
ALTER TABLE ONLY public.bioclim DROP CONSTRAINT bioclim_pkey;
ALTER TABLE public.times ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.scenarios ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.models ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.bioclim ALTER COLUMN id DROP DEFAULT;
DROP SEQUENCE public.times_id_seq;
DROP TABLE public.times;
DROP SEQUENCE public.scenarios_id_seq;
DROP TABLE public.scenarios;
DROP SEQUENCE public.models_id_seq;
DROP TABLE public.models;
DROP SEQUENCE public.bioclim_id_seq;
DROP TABLE public.bioclim;
SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: bioclim; Type: TABLE; Schema: public; Owner: ap02; Tablespace: 
--

CREATE TABLE bioclim (
    id integer NOT NULL,
    dataname character varying(60),
    description character varying(256),
    moreinfo character varying(900),
    uri character varying(500),
    metadata_ref character varying(500),
    update_datetime timestamp without time zone
);


ALTER TABLE public.bioclim OWNER TO ap02;

--
-- Name: bioclim_id_seq; Type: SEQUENCE; Schema: public; Owner: ap02
--

CREATE SEQUENCE bioclim_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.bioclim_id_seq OWNER TO ap02;

--
-- Name: bioclim_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: ap02
--

ALTER SEQUENCE bioclim_id_seq OWNED BY bioclim.id;


--
-- Name: bioclim_id_seq; Type: SEQUENCE SET; Schema: public; Owner: ap02
--

SELECT pg_catalog.setval('bioclim_id_seq', 19, true);


--
-- Name: models; Type: TABLE; Schema: public; Owner: ap02; Tablespace: 
--

CREATE TABLE models (
    id integer NOT NULL,
    dataname character varying(60),
    description character varying(256),
    moreinfo character varying(900),
    uri character varying(500),
    metadata_ref character varying(500),
    update_datetime timestamp without time zone
);


ALTER TABLE public.models OWNER TO ap02;

--
-- Name: models_id_seq; Type: SEQUENCE; Schema: public; Owner: ap02
--

CREATE SEQUENCE models_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.models_id_seq OWNER TO ap02;

--
-- Name: models_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: ap02
--

ALTER SEQUENCE models_id_seq OWNED BY models.id;


--
-- Name: models_id_seq; Type: SEQUENCE SET; Schema: public; Owner: ap02
--

SELECT pg_catalog.setval('models_id_seq', 19, true);


--
-- Name: scenarios; Type: TABLE; Schema: public; Owner: ap02; Tablespace: 
--

CREATE TABLE scenarios (
    id integer NOT NULL,
    dataname character varying(60),
    description character varying(256),
    moreinfo character varying(900),
    uri character varying(500),
    metadata_ref character varying(500),
    update_datetime timestamp without time zone
);


ALTER TABLE public.scenarios OWNER TO ap02;

--
-- Name: scenarios_id_seq; Type: SEQUENCE; Schema: public; Owner: ap02
--

CREATE SEQUENCE scenarios_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.scenarios_id_seq OWNER TO ap02;

--
-- Name: scenarios_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: ap02
--

ALTER SEQUENCE scenarios_id_seq OWNED BY scenarios.id;


--
-- Name: scenarios_id_seq; Type: SEQUENCE SET; Schema: public; Owner: ap02
--

SELECT pg_catalog.setval('scenarios_id_seq', 9, true);


--
-- Name: times; Type: TABLE; Schema: public; Owner: ap02; Tablespace: 
--

CREATE TABLE times (
    id integer NOT NULL,
    dataname character varying(60),
    description character varying(256),
    moreinfo character varying(900),
    uri character varying(500),
    metadata_ref character varying(500),
    update_datetime timestamp without time zone
);


ALTER TABLE public.times OWNER TO ap02;

--
-- Name: times_id_seq; Type: SEQUENCE; Schema: public; Owner: ap02
--

CREATE SEQUENCE times_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.times_id_seq OWNER TO ap02;

--
-- Name: times_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: ap02
--

ALTER SEQUENCE times_id_seq OWNED BY times.id;


--
-- Name: times_id_seq; Type: SEQUENCE SET; Schema: public; Owner: ap02
--

SELECT pg_catalog.setval('times_id_seq', 8, true);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: ap02
--

ALTER TABLE ONLY bioclim ALTER COLUMN id SET DEFAULT nextval('bioclim_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: ap02
--

ALTER TABLE ONLY models ALTER COLUMN id SET DEFAULT nextval('models_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: ap02
--

ALTER TABLE ONLY scenarios ALTER COLUMN id SET DEFAULT nextval('scenarios_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: ap02
--

ALTER TABLE ONLY times ALTER COLUMN id SET DEFAULT nextval('times_id_seq'::regclass);


--
-- Data for Name: bioclim; Type: TABLE DATA; Schema: public; Owner: ap02
--

COPY bioclim (id, dataname, description, moreinfo, uri, metadata_ref, update_datetime) FROM stdin;
1	BIO1 	Annual Mean Temperature	Annual Mean Temperature	http://www.worldclim.org/bioclim	\N	\N
2	BIO2 	Mean Diurnal Range (Mean of monthly (max temp - min temp))	Mean Diurnal Range (Mean of monthly (max temp - min temp))	http://www.worldclim.org/bioclim	\N	\N
3	BIO3 	Isothermality (BIO2/BIO7) (* 100)	Isothermality (BIO2/BIO7) (* 100)	http://www.worldclim.org/bioclim	\N	\N
4	BIO4 	Temperature Seasonality (standard deviation *100)	Temperature Seasonality (standard deviation *100)	http://www.worldclim.org/bioclim	\N	\N
5	BIO5 	Max Temperature of Warmest Month	Max Temperature of Warmest Month	http://www.worldclim.org/bioclim	\N	\N
6	BIO6 	Min Temperature of Coldest Month	Min Temperature of Coldest Month	http://www.worldclim.org/bioclim	\N	\N
7	BIO7 	Temperature Annual Range (BIO5-BIO6)	Temperature Annual Range (BIO5-BIO6)	http://www.worldclim.org/bioclim	\N	\N
8	BIO8 	Mean Temperature of Wettest Quarter	Mean Temperature of Wettest Quarter	http://www.worldclim.org/bioclim	\N	\N
9	BIO9 	Mean Temperature of Driest Quarter	Mean Temperature of Driest Quarter	http://www.worldclim.org/bioclim	\N	\N
10	BIO10 	Mean Temperature of Warmest Quarter	Mean Temperature of Warmest Quarter	http://www.worldclim.org/bioclim	\N	\N
11	BIO11 	Mean Temperature of Coldest Quarter	Mean Temperature of Coldest Quarter	http://www.worldclim.org/bioclim	\N	\N
12	BIO12 	Annual Precipitation	Annual Precipitation	http://www.worldclim.org/bioclim	\N	\N
13	BIO13 	Precipitation of Wettest Month	Precipitation of Wettest Month	http://www.worldclim.org/bioclim	\N	\N
14	BIO14 	Precipitation of Driest Month	Precipitation of Driest Month	http://www.worldclim.org/bioclim	\N	\N
15	BIO15 	Precipitation Seasonality (Coefficient of Variation)	Precipitation Seasonality (Coefficient of Variation)	http://www.worldclim.org/bioclim	\N	\N
16	BIO16 	Precipitation of Wettest Quarter	Precipitation of Wettest Quarter	http://www.worldclim.org/bioclim	\N	\N
17	BIO17 	Precipitation of Driest Quarter	Precipitation of Driest Quarter	http://www.worldclim.org/bioclim	\N	\N
18	BIO18 	Precipitation of Warmest Quarter	Precipitation of Warmest Quarter	http://www.worldclim.org/bioclim	\N	\N
19	BIO19 	Precipitation of Coldest Quarter	Precipitation of Coldest Quarter	http://www.worldclim.org/bioclim	\N	\N
\.


--
-- Data for Name: models; Type: TABLE DATA; Schema: public; Owner: ap02
--

COPY models (id, dataname, description, moreinfo, uri, metadata_ref, update_datetime) FROM stdin;
1	cccma-cgcm31	Coupled Global Climate Model (CGCM3)	Canadian Centre for Climate Modelling and Analysis (CCCma)	http://www.ipcc-data.org/ar4/model-CCCMA-CGCM3_1-T47-change.html	\N	\N
2	ccsr-miroc32hi	MIROC3.2 (hires)	CCSR/NIES/FRCGC, Japan CCSR = Center for Climate System Research, University of Tokyo NIES = National Institute for Environmental Studies FRCGC = Frontier Research Center for Global Chance, Japan Agency for Marine-Earth Science and Technology (JAMSTEC) (The University ofTokyo is a National University Corporation and NIES and JAMSTEC are Independent Administrative Institutions)	http://www-pcmdi.llnl.gov/ipcc/model_documentation/MIROC3.2_hires.pdf	\N	\N
3	ccsr-miroc32med	MIROC3.2 (medres)	CCSR/NIES/FRCGC, Japan CCSR = Center for Climate System Research, University of Tokyo NIES = National Institute for Environmental Studies FRCGC = Frontier Research Center for Global Chance, Japan Agency for Marine-Earth Science and Technology (JAMSTEC) (The University ofTokyo is a National University Corporation and NIES and JAMSTEC are Independent Administrative Institutions)	http://www-pcmdi.llnl.gov/ipcc/model_documentation/MIROC3.2_hires.pdf	\N	\N
4	cnrm-cm3	CNRM-CM3	Centre National de Recherches Meteorologiques, Meteo France, France	http://www.ipcc-data.org/ar4/model-CNRM-CM3-change.html	\N	\N
5	csiro-mk30	CSIRO Mark 3.0	The CSIRO Mk3.5 Climate Model The Centre for Australian Weather and Climate Research	http://www.ipcc-data.org/ar4/model-CSIRO-MK3-change.html	\N	\N
6	gfdl-cm20	CM2.0 - AOGCM	Geophysical Fluid Dynamics Laboratory,NOAA	http://www.ipcc-data.org/ar4/model-GFDL-CM2-change.html	\N	\N
7	gfdl-cm21	CM2.1 - AOGCM	Geophysical Fluid Dynamics Laboratory,NOAA	http://www.ipcc-data.org/ar4/model-GFDL-CM2_1-change.html	\N	\N
8	giss-modeleh	GISS ModelE-H and GISS ModelE-R (which differ only in ocean component)	Goddard Institute for Space Studies (GISS), NASA, USA	http://www.ipcc-data.org/ar4/model-NASA-GISS-EH-change.html	\N	\N
9	giss-modeler	GISS ModelE-H and GISS ModelE-R (which differ only in ocean component)	Goddard Institute for Space Studies (GISS), NASA, USA	http://www.ipcc-data.org/ar4/model-NASA-GISS-ER-change.html	\N	\N
10	iap-fgoals10g	FGOALS1.0_g	LASG, Institute of Atmospheric Physics, Chinese Academy of Sciemces, P.O. Box 9804, Beijing 100029, P.R. China	http://www.ipcc-data.org/ar4/model-LASG-FGOALS-G1_0-change.html	\N	\N
11	inm-cm30	INMCM3.0	Institute of Numerical Mathematics, Russian Academy of Science, Russia.	http://www.ipcc-data.org/ar4/model-INM-CM3-change.html	\N	\N
12	ipsl-cm4	IPSL-CM4	Institut Pierre Simon Laplace (IPSL), France	http://www.ipcc-data.org/ar4/model-IPSL-CM4-change.html	\N	\N
13	mpi-echam5	ECHAM5/MPI-OM	Max Planck Institute for Meteorology, Germany	http://www.ipcc-data.org/ar4/model-MPIM-ECHAM5-change.html	\N	\N
14	mri-cgcm232a	MRI-CGCM2.3.2	Meteorological Research Institute, Japan Meteorological Agency, Japan	http://www.ipcc-data.org/ar4/model-MRI-CGCM2_3_2-change.html	\N	\N
15	ncar-ccsm30	Community Climate System Model, version 3.0 (CCSM3)	National Center for Atmospheric Research (NCAR),	http://www.ipcc-data.org/ar4/model-NCAR-CCSM3-change.html	\N	\N
16	ncar-pcm1	Parallel Climate Model (PCM)	National Center for Atmospheric Research (NCAR), NSF (a primary sponsor), DOE (a primary sponsor), NASA, and NOAA	http://www.ipcc-data.org/ar4/model-NCAR-PCM-change.html	\N	\N
17	ukmo-hadcm3	HadCM3	Hadley Centre for Climate Prediction and Research, Met Office, United Kingdom	http://www.ipcc-data.org/ar4/model-UKMO-HADCM3-change.html	\N	\N
18	ukmo-hadgem1	Hadley Centre Global Environmental Model, version 1 (HadGEM1)	Hadley Centre for Climate Prediction and Research, Met Office United Kingdom	http://www.ipcc-data.org/ar4/model-UKMO-HADGEM1-change.html	\N	\N
19	ALL	Median of All Climate Models			\N	\N
\.


--
-- Data for Name: scenarios; Type: TABLE DATA; Schema: public; Owner: ap02
--

COPY scenarios (id, dataname, description, moreinfo, uri, metadata_ref, update_datetime) FROM stdin;
1	RCP3PD	Low RCP with Peak & Decline (2005-2500)	The RCP 3-PD is developed by the IMAGE modeling team of the Netherlands Environmental Assessment Agency. The emission pathway is representative for scenarios in the literature leading to very low greenhouse gas concentration levels. It is a so-called peak scenario its radiative forcing level first reaches a value around 3.1 W/m2 mid-century returning to 2.6 W/m2 by 2100. In order to reach such radiative forcing levels greenhouse gas emissions (and indirectly emissions of air pollutants) Are reduced substantially over time. The final RCP is based on the publication by Van Vuuren et al. (2007).	http//www.iiasa.ac.at/web-apps/tnt/RcpDb/dsd?Action=htmlpage&page=welcome	\N	\N
2	RCP45	Medium-Low RCP with stabilisation from 2150 onwards (2005-2500)	The RCP 4.5 is developed by the MiniCAM modeling team at the Pacific Northwest National Laboratorys Joint Global Change Research Institute (JGCRI). It is a stabilization scenario where total radiative forcing is stabilized before 2100 by employment of a range of technologies and strategies for reducing greenhouse gas emissions. The scenario drivers and technology options are detailed in Clarke et al. (2007). Additional detail on the simulation of land use and terrestrial carbon emissions is given by Wise et al (2009).	http//www.iiasa.ac.at/web-apps/tnt/RcpDb/dsd?Action=htmlpage&page=welcome	\N	\N
3	RCP6	Medium-High RCP with stabilisation from 2150 onwards (2005-2500)	The RCP 6.0 is developed by the AIM modeling team at the National Institute for Environmental Studies (NIES) Japan. It is a stabilization scenario where total radiative forcing is stabilized after 2100 without overshoot by employment of a range of technologies and strategies for reducing greenhouse gas emissions. The details of the scenario are described in Fujino et al. (2006) And Hijioka et al. (2008).	http//www.iiasa.ac.at/web-apps/tnt/RcpDb/dsd?Action=htmlpage&page=welcome	\N	\N
4	RCP85	High RCP	stabilising emissions post-2100 concentrations post-2200 (2005-2500) The RCP 8.5 is developed by the MESSAGE modeling team and the IIASA Integrated Assessment Framework at the International Institute for Applies Systems Analysis (IIASA) Austria. The RCP 8.5 is characterized by increasing greenhouse gas emissions over time representative for scenarios in the literature leading to high greenhouse gas concentration levels. The underlying scenario drivers and resulting development path are based on the A2r scenario detailed in Riahi et al. (2007).	http//www.iiasa.ac.at/web-apps/tnt/RcpDb/dsd?Action=htmlpage&page=welcome	\N	\N
5	SRESA1B	A1B - A balanced emphasis on all energy sources	Fourth Assessment Report Rapid economic growth. A global population that reaches 9 billion in 2050 and then gradually declines. The quick spread of new and efficient technologies. A convergent world - income and way of life converge between regions. Extensive social and cultural interactions worldwide.	http//en.wikipedia.org/wiki/Special_Report_on_Emissions_Scenarios#Scenario_families	\N	\N
6	SRESA1FI	A1FI - An emphasis on fossil-fuels (Fossil Intensive).		http//en.wikipedia.org/wiki/Special_Report_on_Emissions_Scenarios#Scenario_families	\N	\N
7	SRESA2	A2 – Fourth Assessment Report	A world of independently operating self-reliant nations. Continuously increasing population. Regionally oriented economic development.	http//en.wikipedia.org/wiki/Special_Report_on_Emissions_Scenarios#Scenario_families	\N	\N
8	SRESB1	B1 – Fourth Assessment Report	Rapid economic growth as in A1 but with rapid changes towards a service and information economy. Population rising to 9 billion in 2050 and then declining as in A1. Reductions in material intensity and the introduction of clean and resource efficient technologies. An emphasis on global solutions to economic social and environmental stability	http//en.wikipedia.org/wiki/Special_Report_on_Emissions_Scenarios#Scenario_families	\N	\N
9	SRESB2	B2 – Fourth Assessment Report	Continuously increasing population but at a slower rate than in A2. Emphasis on local rather than global solutions to economic social and environmental stability. Intermediate levels of economic development. Less rapid and more fragmented technological change than in A1 and B1.	http//en.wikipedia.org/wiki/Special_Report_on_Emissions_Scenarios#Scenario_families	\N	\N
\.


--
-- Data for Name: times; Type: TABLE DATA; Schema: public; Owner: ap02
--

COPY times (id, dataname, description, moreinfo, uri, metadata_ref, update_datetime) FROM stdin;
1	2015				\N	\N
2	2025				\N	\N
3	2035				\N	\N
4	2045				\N	\N
5	2055				\N	\N
6	2065				\N	\N
7	2075				\N	\N
8	2085				\N	\N
\.


--
-- Name: bioclim_pkey; Type: CONSTRAINT; Schema: public; Owner: ap02; Tablespace: 
--

ALTER TABLE ONLY bioclim
    ADD CONSTRAINT bioclim_pkey PRIMARY KEY (id);


--
-- Name: models_pkey; Type: CONSTRAINT; Schema: public; Owner: ap02; Tablespace: 
--

ALTER TABLE ONLY models
    ADD CONSTRAINT models_pkey PRIMARY KEY (id);


--
-- Name: scenarios_pkey; Type: CONSTRAINT; Schema: public; Owner: ap02; Tablespace: 
--

ALTER TABLE ONLY scenarios
    ADD CONSTRAINT scenarios_pkey PRIMARY KEY (id);


--
-- Name: times_pkey; Type: CONSTRAINT; Schema: public; Owner: ap02; Tablespace: 
--

ALTER TABLE ONLY times
    ADD CONSTRAINT times_pkey PRIMARY KEY (id);


--
-- Name: bioclim; Type: ACL; Schema: public; Owner: ap02
--

REVOKE ALL ON TABLE bioclim FROM PUBLIC;
REVOKE ALL ON TABLE bioclim FROM ap02;
GRANT ALL ON TABLE bioclim TO ap02;


--
-- Name: bioclim_id_seq; Type: ACL; Schema: public; Owner: ap02
--

REVOKE ALL ON SEQUENCE bioclim_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE bioclim_id_seq FROM ap02;
GRANT ALL ON SEQUENCE bioclim_id_seq TO ap02;


--
-- Name: models; Type: ACL; Schema: public; Owner: ap02
--

REVOKE ALL ON TABLE models FROM PUBLIC;
REVOKE ALL ON TABLE models FROM ap02;
GRANT ALL ON TABLE models TO ap02;


--
-- Name: models_id_seq; Type: ACL; Schema: public; Owner: ap02
--

REVOKE ALL ON SEQUENCE models_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE models_id_seq FROM ap02;
GRANT ALL ON SEQUENCE models_id_seq TO ap02;


--
-- Name: scenarios; Type: ACL; Schema: public; Owner: ap02
--

REVOKE ALL ON TABLE scenarios FROM PUBLIC;
REVOKE ALL ON TABLE scenarios FROM ap02;
GRANT ALL ON TABLE scenarios TO ap02;


--
-- Name: scenarios_id_seq; Type: ACL; Schema: public; Owner: ap02
--

REVOKE ALL ON SEQUENCE scenarios_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE scenarios_id_seq FROM ap02;
GRANT ALL ON SEQUENCE scenarios_id_seq TO ap02;


--
-- Name: times; Type: ACL; Schema: public; Owner: ap02
--

REVOKE ALL ON TABLE times FROM PUBLIC;
REVOKE ALL ON TABLE times FROM ap02;
GRANT ALL ON TABLE times TO ap02;


--
-- Name: times_id_seq; Type: ACL; Schema: public; Owner: ap02
--

REVOKE ALL ON SEQUENCE times_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE times_id_seq FROM ap02;
GRANT ALL ON SEQUENCE times_id_seq TO ap02;


--
-- PostgreSQL database dump complete
--

