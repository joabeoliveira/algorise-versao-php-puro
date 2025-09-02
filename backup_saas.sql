/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.7.2-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: saas_compras
-- ------------------------------------------------------
-- Server version	8.0.42

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `cotacoes_rapidas`
--

DROP TABLE IF EXISTS `cotacoes_rapidas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cotacoes_rapidas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) NOT NULL,
  `criada_por` varchar(255) DEFAULT NULL,
  `criada_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cotacoes_rapidas`
--

LOCK TABLES `cotacoes_rapidas` WRITE;
/*!40000 ALTER TABLE `cotacoes_rapidas` DISABLE KEYS */;
INSERT INTO `cotacoes_rapidas` VALUES
(1,'Cotação de papel','JUlio Alves','2025-07-04 23:30:42'),
(2,'Aquisição de material de expediente','Julio Alves','2025-07-05 13:37:49'),
(3,'Aquisição de papel para impressão','julio alves','2025-07-05 14:45:16'),
(4,'Pesquisa para aquisição de material de expediente','julio alves','2025-07-05 15:42:14'),
(5,'Aquisição de material de expediente para o órgão AAA','julio alves','2025-07-05 19:45:22'),
(6,'Aquisição de papel para impressão','julio alves','2025-07-06 14:57:27'),
(7,'Cotação rápida para papel','julio alves','2025-07-14 21:50:58'),
(8,'Cotação rápida para papel A4','julio alves','2025-08-12 12:58:11');
/*!40000 ALTER TABLE `cotacoes_rapidas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cotacoes_rapidas_itens`
--

DROP TABLE IF EXISTS `cotacoes_rapidas_itens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cotacoes_rapidas_itens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cotacao_rapida_id` int NOT NULL,
  `catmat_catser` varchar(255) NOT NULL,
  `descricao_pesquisa` text NOT NULL,
  `quantidade` int NOT NULL,
  `estatisticas_json` json DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cotacoes_rapidas_itens`
--

LOCK TABLES `cotacoes_rapidas_itens` WRITE;
/*!40000 ALTER TABLE `cotacoes_rapidas_itens` DISABLE KEYS */;
INSERT INTO `cotacoes_rapidas_itens` VALUES
(1,1,'461828','PAPEL PARA IMPRESSÃO FORMATADO, TIPO: SULFITE/APERGAMINHADO/OFÍCIO , TAMANHO (C X L): 297 X 210 MM, GRAMATURA: 75 G/M2, COR: BRANCO , CARACTERÍSTICA ADICIONAL: PH ALCALINO ',1,'{\"media\": 21.866, \"total\": 10, \"maximo\": 25, \"minimo\": 18.79, \"mediana\": 22.065}'),
(2,2,'200069','CANETA ESFEROGRÁFICA, MATERIAL: PLÁSTICO , FORMATO CORPO: CILÍNDRICO , MATERIAL PONTA: PLÁSTICO COM ESFERA DE TUNGSTÊNIO , TIPO ESCRITA: GROSSA , COR TINTA: AZUL ',80,'{\"media\": 11.584110526315788, \"total\": 19, \"maximo\": 81.5881, \"minimo\": 0.4, \"mediana\": 0.68}'),
(3,2,'200081','CANETA ESFEROGRÁFICA, MATERIAL: PLÁSTICO , FORMATO CORPO: SEXTAVADO , MATERIAL PONTA: AÇO INOXIDÁVEL COM ESFERA DE TUNGSTÊNIO , TIPO ESCRITA: GROSSA , COR TINTA: AZUL ',80,'{\"media\": 246.29611111111112, \"total\": 18, \"maximo\": 4294.99, \"minimo\": 0.47, \"mediana\": 0.6799999999999999}'),
(4,2,'425508','LÁPIS PRETO, MATERIAL CORPO: MADEIRA , DUREZA CARGA: 2B , FORMATO CORPO: SEXTAVADO , CARACTERÍSTICAS ADICIONAIS: SEM BORRACHA APAGADORA ',80,'{\"media\": 22.2195, \"total\": 20, \"maximo\": 64.93, \"minimo\": 0.49, \"mediana\": 16.15}'),
(5,2,'461828','PAPEL PARA IMPRESSÃO FORMATADO, TIPO: SULFITE/APERGAMINHADO/OFÍCIO , TAMANHO (C X L): 297 X 210 MM, GRAMATURA: 75 G/M2, COR: BRANCO , CARACTERÍSTICA ADICIONAL: PH ALCALINO ',500,'{\"media\": 22.1395, \"total\": 20, \"maximo\": 25.1, \"minimo\": 18.79, \"mediana\": 22.5}'),
(6,3,'461828','PAPEL PARA IMPRESSÃO FORMATADO, TIPO: SULFITE/APERGAMINHADO/OFÍCIO , TAMANHO (C X L): 297 X 210 MM, GRAMATURA: 75 G/M2, COR: BRANCO , CARACTERÍSTICA ADICIONAL: PH ALCALINO ',500,'{\"media\": 21.98894736842105, \"total\": 19, \"maximo\": 25.1, \"minimo\": 18.79, \"mediana\": 22}'),
(7,4,'461828','PAPEL PARA IMPRESSÃO FORMATADO, TIPO: SULFITE/APERGAMINHADO/OFÍCIO , TAMANHO (C X L): 297 X 210 MM, GRAMATURA: 75 G/M2, COR: BRANCO , CARACTERÍSTICA ADICIONAL: PH ALCALINO ',100,'{\"media\": 22.31578947368421, \"total\": 19, \"maximo\": 25.1, \"minimo\": 19.54, \"mediana\": 23}'),
(8,5,'271022','CANETA ESFEROGRÁFICA, MATERIAL: PLÁSTICO , QUANTIDADE CARGAS: 1 UN, MATERIAL PONTA: LATÃO COM ESFERA DE TUNGSTÊNIO , TIPO ESCRITA: GROSSA , COR TINTA: AZUL , CARACTERÍSTICAS ADICIONAIS: MATERIAL TRANSPARENTE E COM ORIFÍCIO LATERAL ',40,'{\"media\": 1.0066666666666666, \"total\": 6, \"maximo\": 3.05, \"minimo\": 0.44, \"mediana\": 0.585}'),
(9,5,'271023','CANETA ESFEROGRÁFICA, MATERIAL: PLÁSTICO , QUANTIDADE CARGAS: 1 UN, MATERIAL PONTA: LATÃO COM ESFERA DE TUNGSTÊNIO , TIPO ESCRITA: GROSSA , COR TINTA: PRETA , CARACTERÍSTICAS ADICIONAIS: MATERIAL TRANSPARENTE E COM ORIFÍCIO LATERAL ',40,'{\"media\": 0.5, \"total\": 4, \"maximo\": 0.55, \"minimo\": 0.47, \"mediana\": 0.49}'),
(10,5,'461828','PAPEL PARA IMPRESSÃO FORMATADO, TIPO: SULFITE/APERGAMINHADO/OFÍCIO , TAMANHO (C X L): 297 X 210 MM, GRAMATURA: 75 G/M2, COR: BRANCO , CARACTERÍSTICA ADICIONAL: PH ALCALINO ',15,'{\"media\": 21.866, \"total\": 10, \"maximo\": 25, \"minimo\": 18.79, \"mediana\": 22.065}'),
(11,6,'461828','PAPEL PARA IMPRESSÃO FORMATADO, TIPO: SULFITE/APERGAMINHADO/OFÍCIO , TAMANHO (C X L): 297 X 210 MM, GRAMATURA: 75 G/M2, COR: BRANCO , CARACTERÍSTICA ADICIONAL: PH ALCALINO ',100,'{\"media\": 21.983684210526317, \"total\": 19, \"maximo\": 25, \"minimo\": 18.79, \"mediana\": 22}'),
(12,7,'461828','PAPEL PARA IMPRESSÃO FORMATADO, TIPO: SULFITE/APERGAMINHADO/OFÍCIO , TAMANHO (C X L): 297 X 210 MM, GRAMATURA: 75 G/M2, COR: BRANCO , CARACTERÍSTICA ADICIONAL: PH ALCALINO ',1,'{\"media\": 21.76105263157895, \"total\": 19, \"maximo\": 28.72, \"minimo\": 19.54, \"mediana\": 21}'),
(13,8,'461755','PAPEL PARA IMPRESSÃO FORMATADO, TIPO: RECICLADO , TAMANHO (C X L): 297 X 210 MM, GRAMATURA: 75 G/M2, COR: BRANCO ',100,'{\"media\": 22.37666, \"total\": 15, \"maximo\": 30.95, \"minimo\": 16.39, \"mediana\": 20}');
/*!40000 ALTER TABLE `cotacoes_rapidas_itens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cotacoes_rapidas_precos`
--

DROP TABLE IF EXISTS `cotacoes_rapidas_precos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cotacoes_rapidas_precos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cotacao_rapida_item_id` int NOT NULL,
  `fonte_pesquisa` varchar(255) NOT NULL,
  `fornecedor_nome` varchar(255) DEFAULT NULL,
  `data_resultado` date DEFAULT NULL,
  `preco_unitario` decimal(15,2) NOT NULL,
  `considerado` tinyint(1) DEFAULT '1',
  `justificativa_descarte` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=216 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cotacoes_rapidas_precos`
--

LOCK TABLES `cotacoes_rapidas_precos` WRITE;
/*!40000 ALTER TABLE `cotacoes_rapidas_precos` DISABLE KEYS */;
INSERT INTO `cotacoes_rapidas_precos` VALUES
(1,1,'Painel de Preços (Inciso I)','ESP-DR.10 - GDE.SAO PAULO','2025-07-01',18.79,1,''),
(2,1,'Painel de Preços (Inciso I)','SUPERINTENDENCIA REG ADMINISTRACAO DO MGI-SP','2025-06-30',19.77,1,''),
(3,1,'Painel de Preços (Inciso I)','CONSELHO REGIONAL DE MEDICINA - PE','2025-06-30',25.00,1,''),
(4,1,'Painel de Preços (Inciso I)','MEX-11.REGIMENTO DE CAVALARIA MECANIZADO/MS','2025-06-27',23.43,1,''),
(5,1,'Painel de Preços (Inciso I)','MEX-11.REGIMENTO DE CAVALARIA MECANIZADO/MS','2025-06-27',23.45,1,''),
(6,1,'Painel de Preços (Inciso I)','MEX-11.REGIMENTO DE CAVALARIA MECANIZADO/MS','2025-06-27',23.47,1,''),
(7,1,'Painel de Preços (Inciso I)','MEX-11.REGIMENTO DE CAVALARIA MECANIZADO/MS','2025-06-27',23.45,1,''),
(8,1,'Painel de Preços (Inciso I)','ESP-FUNDAÇÃO C.A.S.A. - SEDE ADMINISTRAÇÃO','2025-06-25',20.70,1,''),
(9,1,'Painel de Preços (Inciso I)','ESP-FUNDAÇÃO C.A.S.A. - SEDE ADMINISTRAÇÃO','2025-06-25',19.90,1,''),
(10,1,'Painel de Preços (Inciso I)','ESP-FUNDAÇÃO C.A.S.A. - SEDE ADMINISTRAÇÃO','2025-06-25',20.70,1,''),
(11,2,'Painel de Preços (Inciso I)','PREFEITURA MUNICIPAL DE BOA VISTA - RR','2025-06-25',1.69,1,''),
(12,2,'Painel de Preços (Inciso I)','GOVERNO DO ESTADO DO CEARÁ','2025-05-27',0.65,1,''),
(13,2,'Painel de Preços (Inciso I)','PMSP - HOSPITAL DO SERVIDOR PÚBLICO MUNICIPAL','2025-04-11',0.47,1,''),
(14,2,'Painel de Preços (Inciso I)','PREFEITURA MUNICIPAL DE BOA VISTA DO TUPIM/BA','2025-04-10',16.00,1,''),
(15,2,'Painel de Preços (Inciso I)','4 REGIMENTO DE CARROS DE COMBATE/RS','2025-02-24',26.57,1,''),
(16,2,'Painel de Preços (Inciso I)','ASSOCIAÇAO DE A.C.E. DE CRISTALANDIA/TO','2025-02-03',33.30,0,'valor excessivo '),
(17,2,'Painel de Preços (Inciso I)','ASSOCIAÇAO DE A.E.E.AUGUSTINOPOLIS/TO','2025-01-16',60.41,1,''),
(18,2,'Painel de Preços (Inciso I)','PMSP - SECRETARIA MUNICIPAL SEGURANÇA URBANA','2024-12-18',23.90,1,''),
(19,2,'Painel de Preços (Inciso I)','JUSTICA FEDERAL DE 1A. INSTANCIA - CE','2024-12-10',0.56,1,''),
(20,2,'Painel de Preços (Inciso I)','JUSTICA FEDERAL DE 1A. INSTANCIA - CE','2024-12-10',0.56,1,''),
(21,2,'Contratação Similar (Inciso II)','PORTA HELICOPTEROS MULTIPROPóSITO\"ATLâNTICO\"','2024-11-22',0.40,1,''),
(22,2,'Contratação Similar (Inciso II)','PORTA HELICOPTEROS MULTIPROPóSITO\"ATLâNTICO\"','2024-11-22',0.60,1,''),
(23,2,'Contratação Similar (Inciso II)','BASE DE FUZILEIROS NAVAIS DO RIO MERITI','2024-10-29',2.20,1,''),
(24,2,'Contratação Similar (Inciso II)','MEC-FACULDADE DE MEDICINA DA UF/RJ','2024-10-18',0.68,1,''),
(25,2,'Contratação Similar (Inciso II)','CAIXA DE CONSTRUCÕES DE CASAS P/PESSOAL DA M','2024-10-16',0.60,1,''),
(26,2,'Contratação Similar (Inciso II)','PROCURADORIA REGIONAL DA REPUBLICA-2A.REGIÃO','2024-09-24',1.59,1,''),
(27,2,'Contratação Similar (Inciso II)','DEPOSITO SUPRIMENTOS INTENDENCIA MARINHA RJ','2024-09-17',81.59,1,''),
(28,2,'Contratação Similar (Inciso II)','BASE AEREA NAVAL DE SAO PEDRO DA ALDEIA/RJ','2024-07-11',0.46,1,''),
(29,2,'Contratação Similar (Inciso II)','CENTRO MISSEIS E AR.SUBMAR.ALM.LUIZ A.P.NEVES','2024-06-19',0.69,1,''),
(30,2,'Contratação Similar (Inciso II)','HOSPITAL UNIVERSITARIO DA UFRJ','2024-05-21',0.48,1,''),
(31,3,'Painel de Preços (Inciso I)','CONSELHO REG. CORRETO DE IMOVEIS 4ª REGIAO/MG','2025-06-26',1.83,1,''),
(32,3,'Painel de Preços (Inciso I)','HOSPITAL UNIV. Mª APARECIDA PEDROSSIAN','2025-06-05',0.55,1,''),
(33,3,'Painel de Preços (Inciso I)','TRIBUNAL REGIONAL DO TRABALHO DA 22A. REGIAO','2025-06-02',4294.99,1,''),
(34,3,'Painel de Preços (Inciso I)','ESP-UNESP-FACUL.CIENC.FARMACEUT.-C.ARARAQUARA','2025-05-16',0.47,1,''),
(35,3,'Painel de Preços (Inciso I)','ESP-UNESP-FACUL.CIENC.FARMACEUT.-C.ARARAQUARA','2025-05-16',0.47,1,''),
(36,3,'Painel de Preços (Inciso I)','ESP-UNESP-FACUL.CIENC.FARMACEUT.-C.ARARAQUARA','2025-05-16',0.47,1,''),
(37,3,'Painel de Preços (Inciso I)','PREFEITURA MUNICIPAL DE CAMPO BELO-MG','2025-05-07',0.55,1,''),
(38,3,'Painel de Preços (Inciso I)','FUNDAÇÃO UNIV. FEDERAL DE SÃO JOÃO DEL-REI','2025-04-24',0.49,1,''),
(39,3,'Painel de Preços (Inciso I)','PREFEITURA MUNICIPAL DE RIBEIRAO PRETO - SP','2025-04-01',0.52,1,''),
(40,3,'Painel de Preços (Inciso I)','CENTRO DE INTENDENCIA DA MARINHA EM MANAUS','2025-03-25',19.70,1,''),
(41,3,'Contratação Similar (Inciso II)','CASA DA MOEDA DO BRASIL/MF','2024-08-06',0.62,1,''),
(42,3,'Contratação Similar (Inciso II)','CONSELHO REG DOS REPRESENTANTES COMERCIAIS/RJ','2024-05-10',0.74,1,''),
(43,3,'Contratação Similar (Inciso II)','ODONTOCLINICA CENTRAL','2023-11-30',1.50,1,''),
(44,3,'Contratação Similar (Inciso II)','CENTRO DE INT. DA MARINHA EM PARADA DE LUCAS','2023-09-26',29.00,1,''),
(45,3,'Contratação Similar (Inciso II)','CASA DA MOEDA DO BRASIL/MF','2023-08-24',0.56,1,''),
(46,3,'Contratação Similar (Inciso II)','CONSELHO REG DOS REPRESENTANTES COMERCIAIS/RJ','2023-03-31',30.00,1,''),
(47,3,'Contratação Similar (Inciso II)','DIRETORIA DO PESSOAL MILITAR DA MARINHA','2022-10-14',24.30,1,''),
(48,3,'Contratação Similar (Inciso II)','PREFEITURA MUNICIPAL DE VOLTA REDONDA-RJ','2022-05-25',26.57,1,''),
(49,4,'Painel de Preços (Inciso I)','26 GRUPO DE ARTILHARIA DE CAMPANHA','2025-06-27',6.00,1,''),
(50,4,'Painel de Preços (Inciso I)','CONSELHO REG.DE FIS. E TERAPIA OCUPACIONAL-MS','2025-06-18',2.35,1,''),
(51,4,'Painel de Preços (Inciso I)','CAMARA MUNICIPAL DE PARACAMBI - RJ','2025-06-16',64.93,1,''),
(52,4,'Painel de Preços (Inciso I)','ETO-ASSOCIAÇAO C.C.E.ADA A.TEIXEIRA/GOIATINS','2025-06-11',27.00,1,''),
(53,4,'Painel de Preços (Inciso I)','ESP-CONSELHO ESTADUAL DE EDUCACAO-CEE','2025-06-02',0.50,1,''),
(54,4,'Painel de Preços (Inciso I)','PREFEITURA MUNICIPAL DE NOVAS TEBAS - PR','2025-05-20',42.50,1,''),
(55,4,'Painel de Preços (Inciso I)','CAMARA MUNICIPAL DE SÃO JERÔNIMO - RS','2025-04-28',27.00,1,''),
(56,4,'Painel de Preços (Inciso I)','INST.FED.DE EDUC.,CIENC.E TEC.DE GOIÁS','2025-04-28',6.49,1,''),
(57,4,'Painel de Preços (Inciso I)','PREFEITURA MUNICIPAL DE MACAE - RJ','2025-04-25',12.40,1,''),
(58,4,'Painel de Preços (Inciso I)','CAMARA MUNICIPAL DE ASSIS CHATEABRIAND - PR','2025-04-15',0.49,1,''),
(59,4,'Contratação Similar (Inciso II)','CAMARA MUNICIPAL DE PARACAMBI - RJ','2025-06-16',64.93,1,''),
(60,4,'Contratação Similar (Inciso II)','PREFEITURA MUNICIPAL DE MACAE - RJ','2025-04-25',12.40,1,''),
(61,4,'Contratação Similar (Inciso II)','PREFEITURA MUNICIPAL DE NOVA FRIBURGO - RJ','2025-01-30',26.00,1,''),
(62,4,'Contratação Similar (Inciso II)','CAMARA MUNICIPAL DE NOVA FRIBURGO - RJ','2025-01-15',19.90,1,''),
(63,4,'Contratação Similar (Inciso II)','INST.FED.DE EDUC.,CIENC.E TEC.FLUMINENSE','2024-12-04',0.50,1,''),
(64,4,'Contratação Similar (Inciso II)','CASA DO MARINHEIRO','2024-11-29',49.20,1,''),
(65,4,'Contratação Similar (Inciso II)','MEC-CENTRO CIENC.MAT.E DA NATUREZA DA UF/RJ','2024-10-22',10.99,1,''),
(66,4,'Contratação Similar (Inciso II)','INST.FED.DE EDUC.,CIENC.E TEC.FLUMINENSE','2024-09-25',2.31,1,''),
(67,4,'Contratação Similar (Inciso II)','FUNDAÇÃO RIO DAS OSTRAS DE CULTURA - RJ','2024-09-06',38.50,1,''),
(68,4,'Contratação Similar (Inciso II)','COLEGIO PEDRO II/REITORIA','2024-08-29',30.00,1,''),
(69,5,'Painel de Preços (Inciso I)','ESP-DR.10 - GDE.SAO PAULO','2025-07-01',18.79,1,''),
(70,5,'Painel de Preços (Inciso I)','SUPERINTENDENCIA REG ADMINISTRACAO DO MGI-SP','2025-06-30',19.77,1,''),
(71,5,'Painel de Preços (Inciso I)','CONSELHO REGIONAL DE MEDICINA - PE','2025-06-30',25.00,1,''),
(72,5,'Painel de Preços (Inciso I)','MEX-11.REGIMENTO DE CAVALARIA MECANIZADO/MS','2025-06-27',23.43,1,''),
(73,5,'Painel de Preços (Inciso I)','MEX-11.REGIMENTO DE CAVALARIA MECANIZADO/MS','2025-06-27',23.45,1,''),
(74,5,'Painel de Preços (Inciso I)','MEX-11.REGIMENTO DE CAVALARIA MECANIZADO/MS','2025-06-27',23.47,1,''),
(75,5,'Painel de Preços (Inciso I)','MEX-11.REGIMENTO DE CAVALARIA MECANIZADO/MS','2025-06-27',23.45,1,''),
(76,5,'Painel de Preços (Inciso I)','ESP-FUNDAÇÃO C.A.S.A. - SEDE ADMINISTRAÇÃO','2025-06-25',20.70,1,''),
(77,5,'Painel de Preços (Inciso I)','ESP-FUNDAÇÃO C.A.S.A. - SEDE ADMINISTRAÇÃO','2025-06-25',19.90,1,''),
(78,5,'Painel de Preços (Inciso I)','ESP-FUNDAÇÃO C.A.S.A. - SEDE ADMINISTRAÇÃO','2025-06-25',20.70,1,''),
(79,5,'Contratação Similar (Inciso II)','CAMARA MUNICIPAL DE PARACAMBI - RJ','2025-06-16',22.00,1,''),
(80,5,'Contratação Similar (Inciso II)','PREFEITURA MUNICIPAL DE BARRA DO PIRAI - RJ','2025-06-05',25.10,1,''),
(81,5,'Contratação Similar (Inciso II)','PREFEITURA MUNICIPAL DE BARRA DO PIRAI - RJ','2025-06-05',20.72,1,''),
(82,5,'Contratação Similar (Inciso II)','PREFEITURA MUNICIPAL DE MACAE - RJ','2025-05-26',20.51,1,''),
(83,5,'Contratação Similar (Inciso II)','PREFEITURA MUNICIPAL DO RIO DE JANEIRO - RJ','2025-05-09',24.76,1,''),
(84,5,'Contratação Similar (Inciso II)','PREFEITURA MUNICIPAL DE MACAE - RJ','2025-04-25',23.45,1,''),
(85,5,'Contratação Similar (Inciso II)','INSTITUTO OSWALDO CRUZ','2025-04-17',21.25,1,''),
(86,5,'Contratação Similar (Inciso II)','HOSPITAL UNIVERSITÁRIO GAFFRÉE E GUINLE','2025-02-07',23.80,1,''),
(87,5,'Contratação Similar (Inciso II)','HOSPITAL UNIVERSITÁRIO GAFFRÉE E GUINLE','2025-02-07',23.00,1,''),
(88,5,'Contratação Similar (Inciso II)','PREFEITURA MUNICIPAL DE CANTAGALO - RJ','2025-01-13',19.54,1,''),
(89,6,'Painel de Preços (Inciso I)','ESP-DR.10 - GDE.SAO PAULO','2025-07-01',18.79,1,''),
(90,6,'Painel de Preços (Inciso I)','SUPERINTENDENCIA REG ADMINISTRACAO DO MGI-SP','2025-06-30',19.77,1,''),
(91,6,'Painel de Preços (Inciso I)','CONSELHO REGIONAL DE MEDICINA - PE','2025-06-30',25.00,0,'valor excessivo podendo gerar sobrepreço'),
(92,6,'Painel de Preços (Inciso I)','MEX-11.REGIMENTO DE CAVALARIA MECANIZADO/MS','2025-06-27',23.43,1,''),
(93,6,'Painel de Preços (Inciso I)','MEX-11.REGIMENTO DE CAVALARIA MECANIZADO/MS','2025-06-27',23.45,1,''),
(94,6,'Painel de Preços (Inciso I)','MEX-11.REGIMENTO DE CAVALARIA MECANIZADO/MS','2025-06-27',23.47,1,''),
(95,6,'Painel de Preços (Inciso I)','MEX-11.REGIMENTO DE CAVALARIA MECANIZADO/MS','2025-06-27',23.45,1,''),
(96,6,'Painel de Preços (Inciso I)','ESP-FUNDAÇÃO C.A.S.A. - SEDE ADMINISTRAÇÃO','2025-06-25',20.70,1,''),
(97,6,'Painel de Preços (Inciso I)','ESP-FUNDAÇÃO C.A.S.A. - SEDE ADMINISTRAÇÃO','2025-06-25',19.90,1,''),
(98,6,'Painel de Preços (Inciso I)','ESP-FUNDAÇÃO C.A.S.A. - SEDE ADMINISTRAÇÃO','2025-06-25',20.70,1,''),
(99,6,'Contratação Similar (Inciso II)','CAMARA MUNICIPAL DE PARACAMBI - RJ','2025-06-16',22.00,1,''),
(100,6,'Contratação Similar (Inciso II)','PREFEITURA MUNICIPAL DE BARRA DO PIRAI - RJ','2025-06-05',25.10,1,''),
(101,6,'Contratação Similar (Inciso II)','PREFEITURA MUNICIPAL DE BARRA DO PIRAI - RJ','2025-06-05',20.72,1,''),
(102,6,'Contratação Similar (Inciso II)','PREFEITURA MUNICIPAL DE MACAE - RJ','2025-05-26',20.51,1,''),
(103,6,'Contratação Similar (Inciso II)','PREFEITURA MUNICIPAL DO RIO DE JANEIRO - RJ','2025-05-09',24.76,1,''),
(104,6,'Contratação Similar (Inciso II)','PREFEITURA MUNICIPAL DE MACAE - RJ','2025-04-25',23.45,1,''),
(105,6,'Contratação Similar (Inciso II)','INSTITUTO OSWALDO CRUZ','2025-04-17',21.25,1,''),
(106,6,'Contratação Similar (Inciso II)','HOSPITAL UNIVERSITÁRIO GAFFRÉE E GUINLE','2025-02-07',23.80,1,''),
(107,6,'Contratação Similar (Inciso II)','HOSPITAL UNIVERSITÁRIO GAFFRÉE E GUINLE','2025-02-07',23.00,1,''),
(108,6,'Contratação Similar (Inciso II)','PREFEITURA MUNICIPAL DE CANTAGALO - RJ','2025-01-13',19.54,1,''),
(109,7,'Painel de Preços (Inciso I)','ESP-DR.10 - GDE.SAO PAULO','2025-07-01',18.79,0,'Considerando ser um preço inexequível'),
(110,7,'Painel de Preços (Inciso I)','SUPERINTENDENCIA REG ADMINISTRACAO DO MGI-SP','2025-06-30',19.77,1,''),
(111,7,'Painel de Preços (Inciso I)','CONSELHO REGIONAL DE MEDICINA - PE','2025-06-30',25.00,1,''),
(112,7,'Painel de Preços (Inciso I)','MEX-11.REGIMENTO DE CAVALARIA MECANIZADO/MS','2025-06-27',23.43,1,''),
(113,7,'Painel de Preços (Inciso I)','MEX-11.REGIMENTO DE CAVALARIA MECANIZADO/MS','2025-06-27',23.45,1,''),
(114,7,'Painel de Preços (Inciso I)','MEX-11.REGIMENTO DE CAVALARIA MECANIZADO/MS','2025-06-27',23.47,1,''),
(115,7,'Painel de Preços (Inciso I)','MEX-11.REGIMENTO DE CAVALARIA MECANIZADO/MS','2025-06-27',23.45,1,''),
(116,7,'Painel de Preços (Inciso I)','ESP-FUNDAÇÃO C.A.S.A. - SEDE ADMINISTRAÇÃO','2025-06-25',20.70,1,''),
(117,7,'Painel de Preços (Inciso I)','ESP-FUNDAÇÃO C.A.S.A. - SEDE ADMINISTRAÇÃO','2025-06-25',19.90,1,''),
(118,7,'Painel de Preços (Inciso I)','ESP-FUNDAÇÃO C.A.S.A. - SEDE ADMINISTRAÇÃO','2025-06-25',20.70,1,''),
(119,7,'Contratação Similar (Inciso II)','CAMARA MUNICIPAL DE PARACAMBI - RJ','2025-06-16',22.00,1,''),
(120,7,'Contratação Similar (Inciso II)','PREFEITURA MUNICIPAL DE BARRA DO PIRAI - RJ','2025-06-05',25.10,1,''),
(121,7,'Contratação Similar (Inciso II)','PREFEITURA MUNICIPAL DE BARRA DO PIRAI - RJ','2025-06-05',20.72,1,''),
(122,7,'Contratação Similar (Inciso II)','PREFEITURA MUNICIPAL DE MACAE - RJ','2025-05-26',20.51,1,''),
(123,7,'Contratação Similar (Inciso II)','PREFEITURA MUNICIPAL DO RIO DE JANEIRO - RJ','2025-05-09',24.76,1,''),
(124,7,'Contratação Similar (Inciso II)','PREFEITURA MUNICIPAL DE MACAE - RJ','2025-04-25',23.45,1,''),
(125,7,'Contratação Similar (Inciso II)','INSTITUTO OSWALDO CRUZ','2025-04-17',21.25,1,''),
(126,7,'Contratação Similar (Inciso II)','HOSPITAL UNIVERSITÁRIO GAFFRÉE E GUINLE','2025-02-07',23.80,1,''),
(127,7,'Contratação Similar (Inciso II)','HOSPITAL UNIVERSITÁRIO GAFFRÉE E GUINLE','2025-02-07',23.00,1,''),
(128,7,'Contratação Similar (Inciso II)','PREFEITURA MUNICIPAL DE CANTAGALO - RJ','2025-01-13',19.54,1,''),
(129,8,'Painel de Preços (Inciso I)','COMANDO DO GRUPAMENTO NAVAL DO SUL/RS','2025-06-27',3.05,1,''),
(130,8,'Painel de Preços (Inciso I)','CENTRO DE FORMACAO DE PROFESSORES','2025-06-27',27.99,0,'Unidade de medida diferente'),
(131,8,'Painel de Preços (Inciso I)','CONSELHO REGIONAL DE ENFERMAGEM-MA','2025-06-17',0.88,1,''),
(132,8,'Painel de Preços (Inciso I)','SECRETARIA DE ESTADO DO DESENV. AMBIENTAL','2025-05-26',49.00,0,'Unidade de medida diferente'),
(133,8,'Painel de Preços (Inciso I)','ESP-UNESP-FACUL. CIEN. TECNOL EDUC-C.OURINHOS','2025-04-29',44.53,0,'Unidade de medida diferente'),
(134,8,'Painel de Preços (Inciso I)','ESP-UNESP-FACULARQUIT,ARTES E COMUM.-C.BAURU','2025-03-18',0.50,1,''),
(135,8,'Painel de Preços (Inciso I)','CONSELHO REGIONAL DE MEDICINA VETERINARIA-RO','2025-03-17',0.67,1,''),
(136,8,'Painel de Preços (Inciso I)','ESP-UNESP-FACULARQUIT,ARTES E COMUM.-C.BAURU','2025-03-06',0.50,1,''),
(137,8,'Painel de Preços (Inciso I)','CONSELHO REGIONAL DE MEDICINA VETERINARIA-PE','2025-02-26',27.50,0,'Unidade de medida diferente'),
(138,8,'Painel de Preços (Inciso I)','UNIVERSIDADE FEDERAL DO ESPIRITO SANTO/ES','2025-01-31',0.44,1,''),
(139,9,'Painel de Preços (Inciso I)','CENTRO DE FORMACAO DE PROFESSORES','2025-06-27',27.00,0,'Unidade de medida diferente'),
(140,9,'Painel de Preços (Inciso I)','13.REG.TRIBUNAL REGIONAL DO TRABALHO/PB','2025-06-13',6.29,0,'Unidade de medida diferente'),
(141,9,'Painel de Preços (Inciso I)','HOSPITAL UNIV. Mª APARECIDA PEDROSSIAN','2025-06-05',0.49,1,''),
(142,9,'Painel de Preços (Inciso I)','SECRETARIA DE ESTADO DO DESENV. AMBIENTAL','2025-05-26',49.00,0,'Unidade de medida diferente'),
(143,9,'Painel de Preços (Inciso I)','PREFEITURA MUNICIPAL DE CAMPO BELO-MG','2025-05-07',0.55,1,''),
(144,9,'Painel de Preços (Inciso I)','FUNDAÇÃO UNIV. FEDERAL DE SÃO JOÃO DEL-REI','2025-04-24',0.49,1,''),
(145,9,'Painel de Preços (Inciso I)','PMSP - HOSPITAL DO SERVIDOR PÚBLICO MUNICIPAL','2025-04-11',0.47,1,''),
(146,9,'Painel de Preços (Inciso I)','COLEGIO MILITAR DE FORTALEZA/MEX - CE','2025-04-01',25.56,0,'Unidade de medida diferente'),
(147,9,'Painel de Preços (Inciso I)','CENTRO DE INTENDENCIA DA MARINHA EM MANAUS','2025-03-25',21.07,0,'Unidade de medida diferente'),
(148,9,'Painel de Preços (Inciso I)','CENTRO DE INTENDENCIA DA MARINHA EM MANAUS','2025-03-25',19.30,0,'Unidade de medida diferente'),
(149,10,'Painel de Preços (Inciso I)','ESP-DR.10 - GDE.SAO PAULO','2025-07-01',18.79,1,''),
(150,10,'Painel de Preços (Inciso I)','SUPERINTENDENCIA REG ADMINISTRACAO DO MGI-SP','2025-06-30',19.77,1,''),
(151,10,'Painel de Preços (Inciso I)','CONSELHO REGIONAL DE MEDICINA - PE','2025-06-30',25.00,1,''),
(152,10,'Painel de Preços (Inciso I)','MEX-11.REGIMENTO DE CAVALARIA MECANIZADO/MS','2025-06-27',23.43,1,''),
(153,10,'Painel de Preços (Inciso I)','MEX-11.REGIMENTO DE CAVALARIA MECANIZADO/MS','2025-06-27',23.45,1,''),
(154,10,'Painel de Preços (Inciso I)','MEX-11.REGIMENTO DE CAVALARIA MECANIZADO/MS','2025-06-27',23.47,1,''),
(155,10,'Painel de Preços (Inciso I)','MEX-11.REGIMENTO DE CAVALARIA MECANIZADO/MS','2025-06-27',23.45,1,''),
(156,10,'Painel de Preços (Inciso I)','ESP-FUNDAÇÃO C.A.S.A. - SEDE ADMINISTRAÇÃO','2025-06-25',20.70,1,''),
(157,10,'Painel de Preços (Inciso I)','ESP-FUNDAÇÃO C.A.S.A. - SEDE ADMINISTRAÇÃO','2025-06-25',19.90,1,''),
(158,10,'Painel de Preços (Inciso I)','ESP-FUNDAÇÃO C.A.S.A. - SEDE ADMINISTRAÇÃO','2025-06-25',20.70,1,''),
(159,11,'Painel de Preços (Inciso I)','ESP-DR.10 - GDE.SAO PAULO','2025-07-01',18.79,1,''),
(160,11,'Painel de Preços (Inciso I)','SUPERINTENDENCIA REG ADMINISTRACAO DO MGI-SP','2025-06-30',19.77,1,''),
(161,11,'Painel de Preços (Inciso I)','CONSELHO REGIONAL DE MEDICINA - PE','2025-06-30',25.00,1,''),
(162,11,'Painel de Preços (Inciso I)','MEX-11.REGIMENTO DE CAVALARIA MECANIZADO/MS','2025-06-27',23.43,1,''),
(163,11,'Painel de Preços (Inciso I)','MEX-11.REGIMENTO DE CAVALARIA MECANIZADO/MS','2025-06-27',23.45,1,''),
(164,11,'Painel de Preços (Inciso I)','MEX-11.REGIMENTO DE CAVALARIA MECANIZADO/MS','2025-06-27',23.47,1,''),
(165,11,'Painel de Preços (Inciso I)','MEX-11.REGIMENTO DE CAVALARIA MECANIZADO/MS','2025-06-27',23.45,1,''),
(166,11,'Painel de Preços (Inciso I)','ESP-FUNDAÇÃO C.A.S.A. - SEDE ADMINISTRAÇÃO','2025-06-25',20.70,1,''),
(167,11,'Painel de Preços (Inciso I)','ESP-FUNDAÇÃO C.A.S.A. - SEDE ADMINISTRAÇÃO','2025-06-25',19.90,1,''),
(168,11,'Painel de Preços (Inciso I)','ESP-FUNDAÇÃO C.A.S.A. - SEDE ADMINISTRAÇÃO','2025-06-25',20.70,1,''),
(169,11,'Contratação Similar (Inciso II)','CAMARA MUNICIPAL DE PARACAMBI - RJ','2025-06-16',22.00,1,''),
(170,11,'Contratação Similar (Inciso II)','PREFEITURA MUNICIPAL DE BARRA DO PIRAI - RJ','2025-06-05',25.10,0,'valor excessivo'),
(171,11,'Contratação Similar (Inciso II)','PREFEITURA MUNICIPAL DE BARRA DO PIRAI - RJ','2025-06-05',20.72,1,''),
(172,11,'Contratação Similar (Inciso II)','PREFEITURA MUNICIPAL DE MACAE - RJ','2025-05-26',20.51,1,''),
(173,11,'Contratação Similar (Inciso II)','PREFEITURA MUNICIPAL DO RIO DE JANEIRO - RJ','2025-05-09',24.76,1,''),
(174,11,'Contratação Similar (Inciso II)','PREFEITURA MUNICIPAL DE MACAE - RJ','2025-04-25',23.45,1,''),
(175,11,'Contratação Similar (Inciso II)','INSTITUTO OSWALDO CRUZ','2025-04-17',21.25,1,''),
(176,11,'Contratação Similar (Inciso II)','HOSPITAL UNIVERSITÁRIO GAFFRÉE E GUINLE','2025-02-07',23.80,1,''),
(177,11,'Contratação Similar (Inciso II)','HOSPITAL UNIVERSITÁRIO GAFFRÉE E GUINLE','2025-02-07',23.00,1,''),
(178,11,'Contratação Similar (Inciso II)','PREFEITURA MUNICIPAL DE CANTAGALO - RJ','2025-01-13',19.54,1,''),
(179,12,'Painel de Preços (Inciso I)','ESP-PENIT. NELSON MARCONDES DO AMARAL','2025-07-11',19.65,1,''),
(180,12,'Painel de Preços (Inciso I)','ESP-HOSP.REG.DR.O.F.COELHO,EM F.DE VASCONCELO','2025-07-10',17.00,0,'preço abaixo do estimado'),
(181,12,'Painel de Preços (Inciso I)','PREFEITURA MUNICIPAL DE FLEXEIRAS - AL','2025-07-10',19.99,1,''),
(182,12,'Painel de Preços (Inciso I)','ESP-HOSP.REG.DR.O.F.COELHO,EM F.DE VASCONCELO','2025-07-10',19.58,1,''),
(183,12,'Painel de Preços (Inciso I)','CAMARA MUNICIPAL DE DOIS VIZINHOS - PR','2025-07-10',21.23,1,''),
(184,12,'Painel de Preços (Inciso I)','ESP-PENIT.\"DR.ENIO MENDES JR.\" DE CAP DO ALTO','2025-07-10',19.85,1,''),
(185,12,'Painel de Preços (Inciso I)','HOSPITAL NAVAL DE BRASILIA','2025-07-10',21.00,1,''),
(186,12,'Painel de Preços (Inciso I)','ESP-CDP. \"ASP FRANCIS CARLOS CANESCHI\", BAURU','2025-07-08',19.75,1,''),
(187,12,'Painel de Preços (Inciso I)','TRIBUNAL REGIONAL DO TRABALHO DA 3ª REGIÃO','2025-07-07',28.72,1,''),
(188,12,'Painel de Preços (Inciso I)','ESP-FUND.SISTEMA ESTADUAL ANAL.DADOS-SEADE','2025-07-07',19.56,1,''),
(189,12,'Contratação Similar (Inciso II)','CAMARA MUNICIPAL DE PARACAMBI - RJ','2025-06-16',22.00,1,''),
(190,12,'Contratação Similar (Inciso II)','PREFEITURA MUNICIPAL DE BARRA DO PIRAI - RJ','2025-06-05',20.72,1,''),
(191,12,'Contratação Similar (Inciso II)','PREFEITURA MUNICIPAL DE BARRA DO PIRAI - RJ','2025-06-05',25.10,1,''),
(192,12,'Contratação Similar (Inciso II)','PREFEITURA MUNICIPAL DE MACAE - RJ','2025-05-26',20.51,1,''),
(193,12,'Contratação Similar (Inciso II)','PREFEITURA MUNICIPAL DO RIO DE JANEIRO - RJ','2025-05-09',24.76,1,''),
(194,12,'Contratação Similar (Inciso II)','PREFEITURA MUNICIPAL DE MACAE - RJ','2025-04-25',23.45,1,''),
(195,12,'Contratação Similar (Inciso II)','INSTITUTO OSWALDO CRUZ','2025-04-17',21.25,1,''),
(196,12,'Contratação Similar (Inciso II)','HOSPITAL UNIVERSITÁRIO GAFFRÉE E GUINLE','2025-02-07',23.80,1,''),
(197,12,'Contratação Similar (Inciso II)','HOSPITAL UNIVERSITÁRIO GAFFRÉE E GUINLE','2025-02-07',23.00,1,''),
(198,12,'Contratação Similar (Inciso II)','PREFEITURA MUNICIPAL DE CANTAGALO - RJ','2025-01-13',19.54,1,''),
(199,13,'Painel de Preços (Inciso I)','PREFEITURA MUNICIPAL DE LIMOEIRO - PE','2025-08-07',25.47,1,''),
(200,13,'Painel de Preços (Inciso I)','PREFEITURA MUNICIPAL DE LIMOEIRO - PE','2025-08-07',25.47,1,''),
(201,13,'Painel de Preços (Inciso I)','PREFEITURA MUNICIPAL DE LIMOEIRO - PE','2025-08-07',7.67,0,'abaixo do mercado'),
(202,13,'Painel de Preços (Inciso I)','CONSELHO REG. DE MEDICINA VETERINARIA','2025-07-21',29.00,1,''),
(203,13,'Painel de Preços (Inciso I)','PREFEITURA MUNICIPAL DE CARPINA/PE','2025-07-09',19.90,1,''),
(204,13,'Painel de Preços (Inciso I)','PREFEITURA MUNICIPAL DE CARPINA/PE','2025-07-09',6.91,0,'abaixo do mercado'),
(205,13,'Painel de Preços (Inciso I)','PREFEITURA MUNICIPAL DE CARPINA/PE','2025-07-09',19.90,1,''),
(206,13,'Painel de Preços (Inciso I)','UNIVERSIDADE FEDERAL DO RIO GRANDE DO NORTE','2025-06-27',24.80,1,''),
(207,13,'Painel de Preços (Inciso I)','ESP-ESCOLA DE ENGENHARIA DE SÃO CARLOS -USP','2025-06-18',20.00,1,''),
(208,13,'Painel de Preços (Inciso I)','ESP-ESCOLA DE ENGENHARIA DE SÃO CARLOS -USP','2025-06-18',20.00,1,''),
(209,13,'Contratação Similar (Inciso II)','INSTITUTO NACIONAL DE TRAUMATO-ORTOPEDIA','2024-10-24',23.39,1,''),
(210,13,'Contratação Similar (Inciso II)','PREFEITURA MUNICIPAL DE PORTO REAL - RJ','2024-08-22',18.98,1,''),
(211,13,'Contratação Similar (Inciso II)','CONSELHO FEDERAL DE EDUCAÇÃO FISICA','2024-07-12',23.40,1,''),
(212,13,'Contratação Similar (Inciso II)','CENTRO DE SINALIZ.NAUTICA/REP.ALM.MORAES REGO','2022-07-29',30.95,1,''),
(213,13,'Contratação Similar (Inciso II)','MEC-INSTITUTO DE PSIQUIATRIA DA UF/RJ','2022-06-07',20.00,1,''),
(214,13,'Contratação Similar (Inciso II)','PREFEITURA MUNICIPAL DE NOVA IGUAÇU - RJ','2022-02-01',16.39,1,''),
(215,13,'Contratação Similar (Inciso II)','ODONTOCLINICA CENTRAL DO EXERCITO','2021-12-02',18.00,1,'');
/*!40000 ALTER TABLE `cotacoes_rapidas_precos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fornecedores`
--

DROP TABLE IF EXISTS `fornecedores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `fornecedores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `razao_social` varchar(255) NOT NULL,
  `cnpj` varchar(18) NOT NULL,
  `email` varchar(255) NOT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `ramo_atividade` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cnpj` (`cnpj`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fornecedores`
--

LOCK TABLES `fornecedores` WRITE;
/*!40000 ALTER TABLE `fornecedores` DISABLE KEYS */;
INSERT INTO `fornecedores` VALUES
(1,'Alfa Suprimentos e Comércio EIRELI','11.000.111/0001-57','joabeantonio@gmail.com','Rua Felisbelo Freire 445','(21) 97322-1936','Material de escritório'),
(2,'Alfa Suprimentos & TI','11222333000144','contato@alfa.com','Rua das Inovações, 10, Rio de Janeiro - RJ','21988776655','Informática, Material de escritório'),
(3,'Beta Comércio de Limpeza','44555666000177','vendas@betalimpeza.com','Avenida Paulista, 500, São Paulo - SP','11977665544','Material de limpeza, Copa e cozinha'),
(4,'Gama Serviços Gráficos','77888999000199','grafica@gama.com.br','Rua da Bahia, 1200, Belo Horizonte - MG','31966554433','Serviços Gráficos, Comunicação Visual'),
(5,'Delta Equipamentos Médicos','10111213000122','comercial@deltamed.com','Av. Ipiranga, 3000, Porto Alegre - RS','51955443322','Equipamentos Médicos');
/*!40000 ALTER TABLE `fornecedores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `itens`
--

DROP TABLE IF EXISTS `itens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `itens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `processo_id` int NOT NULL,
  `numero_item` int NOT NULL,
  `catmat_catser` varchar(255) DEFAULT NULL,
  `descricao` text NOT NULL,
  `unidade_medida` varchar(50) NOT NULL,
  `quantidade` int NOT NULL,
  `valor_estimado` decimal(15,2) DEFAULT NULL,
  `metodologia_estimativa` varchar(255) DEFAULT NULL,
  `justificativa_estimativa` text,
  `justificativa_excepcionalidade` text,
  `status_analise` enum('pendente','analisado') NOT NULL DEFAULT 'pendente',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `itens`
--

LOCK TABLES `itens` WRITE;
/*!40000 ALTER TABLE `itens` DISABLE KEYS */;
INSERT INTO `itens` VALUES
(1,1,1,'200069','CANETA ESFEROGRÁFICA, MATERIAL:PLÁSTICO, FORMATO CORPO:CILÍNDRICO, MATERIAL PONTA:PLÁSTICO COM ESFERA DE TUNGSTÊNIO, TIPO ESCRITA:GROSSA, COR TINTA:AZUL','UN',120,0.76,'Média','Considerando a necessidade de evitar item deserto',NULL,'pendente'),
(2,1,2,'200081','CANETA ESFEROGRÁFICA, MATERIAL:PLÁSTICO, FORMATO CORPO:SEXTAVADO, MATERIAL PONTA:AÇO INOXIDÁVEL COM ESFERA DE TUNGSTÊNIO, TIPO ESCRITA:GROSSA, COR TINTA:AZUL','UN',100,0.86,'Média','Considerando a necessidade de evitar item deserto',NULL,'pendente'),
(3,1,3,'200084','CANETA ESFEROGRÁFICA, MATERIAL:PLÁSTICO, FORMATO CORPO:SEXTAVADO, MATERIAL PONTA:AÇO INOXIDÁVEL COM ESFERA DE TUNGSTÊNIO, TIPO ESCRITA:GROSSA, COR TINTA:VERMELHA','UN',100,0.75,'Média','Considerando a necessidade de evitar item deserto',NULL,'pendente'),
(4,1,4,'461828','PAPEL PARA IMPRESSÃO FORMATADO, TIPO:SULFITE/APERGAMINHADO/OFÍCIO, TAMANHO (C X L):297 X 210 MM, GRAMATURA:75 G/M2, COR:BRANCO, CARACTERÍSTICA ADICIONAL:PH ALCALINO','FL',30000,20.42,'Mediana','Considerando a necessidade de evitar item deserto',NULL,'pendente'),
(5,1,5,'425508','LÁPIS PRETO, MATERIAL CORPO:MADEIRA, DUREZA CARGA:2B, FORMATO CORPO:SEXTAVADO, CARACTERÍSTICAS ADICIONAIS:SEM BORRACHA APAGADORA','UN',100,0.52,'Média','Considerando a necessidade de evitar item deserto',NULL,'pendente'),
(6,2,1,'331053','CANETA ESFEROGRÁFICA, MATERIAL:PLÁSTICO, MATERIAL PONTA:ESFERA DE TUNGSTÊNIO, TIPO ESCRITA:GROSSA, COR TINTA:AZUL','UN',50,0.96,'Média','Evitar item deserto',NULL,'analisado'),
(7,3,1,'331053','CANETA ESFEROGRÁFICA, MATERIAL:PLÁSTICO, MATERIAL PONTA:ESFERA DE TUNGSTÊNIO, TIPO ESCRITA:GROSSA, COR TINTA:AZUL','UNIDADE',100,0.99,'Mediana','Considerando a necessidade de se evitar item deserto',NULL,'analisado'),
(8,3,2,'461828','PAPEL PARA IMPRESSÃO FORMATADO, TIPO:SULFITE/APERGAMINHADO/OFÍCIO, TAMANHO (C X L):297 X 210 MM, GRAMATURA:75 G/M2, COR:BRANCO, CARACTERÍSTICA ADICIONAL:PH ALCALINO','EMB c/ 500 FL',100,22.36,'Mediana','Considerando a necessidade de se evitar item deserto',NULL,'analisado'),
(9,3,3,'389773','RÉGUA COMUM, MATERIAL:PLÁSTICO RECICLADO, COMPRIMENTO:20 MM, GRADUAÇÃO:MILIMETRADA','UNIDADE',50,1.22,'Média','Considerando a necessidade de se evitar item deserto foi escolhida a média',NULL,'analisado'),
(10,4,1,'461828','NOTEBOOK, PROCESSADOR I5, 8GB RAM, 256GB SSD','Unidade',15,4250.50,'Mediana',NULL,NULL,'analisado'),
(11,5,1,'891503','DETERGENTE LÍQUIDO NEUTRO 5L','Galão',50,22.80,'Média',NULL,NULL,'analisado'),
(12,5,2,'793033','SACO DE LIXO 100L REFORÇADO','Pacote',100,15.40,'Menor Valor',NULL,NULL,'analisado'),
(13,6,1,'581001','IMPRESSÃO DE FOLDER A4, 4X4 CORES, PAPEL COUCHÊ 150G','Milheiro',5,NULL,NULL,NULL,NULL,''),
(14,7,1,'461829','COMPUTADOR DESKTOP, CORE I7, 16GB RAM, 512GB SSD','Unidade',10,NULL,NULL,NULL,NULL,''),
(15,7,2,'461830','MONITOR LED 24 POLEGADAS, FULL HD, HDMI','Unidade',10,NULL,NULL,NULL,NULL,''),
(16,8,1,'890513','CAFÉ EM PÓ TRADICIONAL 1KG','KG',30,35.75,'Mediana',NULL,NULL,'analisado'),
(17,11,1,'200069','CANETA ESFEROGRÁFICA, MATERIAL:PLÁSTICO, FORMATO CORPO:CILÍNDRICO, MATERIAL PONTA:PLÁSTICO COM ESFERA DE TUNGSTÊNIO, TIPO ESCRITA:GROSSA, COR TINTA:AZUL','UN',100,1.12,'Média','Para evitar item deserto',NULL,'analisado'),
(18,11,2,'200081','CANETA ESFEROGRÁFICA, MATERIAL:PLÁSTICO, FORMATO CORPO:SEXTAVADO, MATERIAL PONTA:AÇO INOXIDÁVEL COM ESFERA DE TUNGSTÊNIO, TIPO ESCRITA:GROSSA, COR TINTA:AZUL','UN',100,1.07,'Média','Para evitar item deserto',NULL,'analisado'),
(19,11,3,'461828','PAPEL PARA IMPRESSÃO FORMATADO, TIPO:SULFITE/APERGAMINHADO/OFÍCIO, TAMANHO (C X L):297 X 210 MM, GRAMATURA:75 G/M2, COR:BRANCO, CARACTERÍSTICA ADICIONAL:PH ALCALINO','FL',30000,20.72,'Mediana','Para evitar item deserto',NULL,'analisado'),
(20,11,4,'425508','LÁPIS PRETO, MATERIAL CORPO:MADEIRA, DUREZA CARGA:2B, FORMATO CORPO:SEXTAVADO, CARACTERÍSTICAS ADICIONAIS:SEM BORRACHA APAGADORA','UN',100,0.97,'Média','Para evitar item deserto',NULL,'analisado'),
(29,12,1,'200069','CANETA ESFEROGRÁFICA, MATERIAL:PLÁSTICO, FORMATO CORPO:CILÍNDRICO, MATERIAL PONTA:PLÁSTICO COM ESFERA DE TUNGSTÊNIO, TIPO ESCRITA:GROSSA, COR TINTA:AZUL','UN',100,1.12,'Média','Escolha dessa metodologia para evitar licitação deserta',NULL,'analisado'),
(30,12,2,'200081','CANETA ESFEROGRÁFICA, MATERIAL:PLÁSTICO, FORMATO CORPO:SEXTAVADO, MATERIAL PONTA:AÇO INOXIDÁVEL COM ESFERA DE TUNGSTÊNIO, TIPO ESCRITA:GROSSA, COR TINTA:AZUL','UN',100,1.07,'Média','Escolha dessa metodologia para evitar licitação deserta',NULL,'analisado'),
(31,12,3,'461828','PAPEL PARA IMPRESSÃO FORMATADO, TIPO:SULFITE/APERGAMINHADO/OFÍCIO, TAMANHO (C X L):297 X 210 MM, GRAMATURA:75 G/M2, COR:BRANCO, CARACTERÍSTICA ADICIONAL:PH ALCALINO','FL',30000,21.98,'Média','Escolha dessa metodologia para evitar licitação deserta',NULL,'analisado'),
(32,12,4,'425508','LÁPIS PRETO, MATERIAL CORPO:MADEIRA, DUREZA CARGA:2B, FORMATO CORPO:SEXTAVADO, CARACTERÍSTICAS ADICIONAIS:SEM BORRACHA APAGADORA','UN',100,1.12,'Média','Escolha dessa metodologia para evitar licitação deserta',NULL,'analisado'),
(33,10,1,'481451','CADEIRA ESCRITÓRIO, MATERIAL ESTRUTURA:AÇO, MATERIAL REVESTIMENTO ASSENTO E ENCOSTO:TECIDO EM POLIPROPILENO, MATERIAL ENCOSTO:ESPUMA INJETADA, TIPO BASE:GIRATÓRIA COM 5 RODÍZIOS','UN',10,NULL,NULL,NULL,NULL,'pendente'),
(34,10,2,'481447','MESA ESCRITÓRIO, MATERIAL ESTRUTURA:MDF, LARGURA:1,20 M, ALTURA:750 CM','UN',10,NULL,NULL,NULL,NULL,'pendente'),
(35,13,1,'461828','PAPEL PARA IMPRESSÃO FORMATADO, TIPO:SULFITE/APERGAMINHADO/OFÍCIO, TAMANHO (C X L):297 X 210 MM, GRAMATURA:75 G/M2, COR:BRANCO, CARACTERÍSTICA ADICIONAL:PH ALCALINO','EMB c/ 500 FL',500,22.77,'Média','Para evitar item deserto na licitação',NULL,'analisado'),
(40,14,1,'461828','PAPEL PARA IMPRESSÃO FORMATADO, TIPO:SULFITE/APERGAMINHADO/OFÍCIO, TAMANHO (C X L):297 X 210 MM, GRAMATURA:75 G/M2, COR:BRANCO, CARACTERÍSTICA ADICIONAL:PH ALCALINO','EMB c/ 500 FL',100,22.29,'Média','Para evitar licitação deserta',NULL,'analisado'),
(41,15,1,'461828','PAPEL PARA IMPRESSÃO FORMATADO, TIPO:SULFITE/APERGAMINHADO/OFÍCIO, TAMANHO (C X L):297 X 210 MM, GRAMATURA:75 G/M2, COR:BRANCO, CARACTERÍSTICA ADICIONAL:PH ALCALINO','EMB c/ 500 FL',300,22.06,'Média','Para evitar licitação deserta',NULL,'analisado'),
(42,16,1,'461783','PAPEL PARA IMPRESSÃO FORMATADO, TIPO:COUCHÊ, TAMANHO (C X L):297 X 210 MM, GRAMATURA:110 G/M2, COR:BRANCO','resma',100,28.00,'Mediana','Para evitar licitação deserta',NULL,'analisado');
/*!40000 ALTER TABLE `itens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lotes_solicitacao`
--

DROP TABLE IF EXISTS `lotes_solicitacao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `lotes_solicitacao` (
  `id` int NOT NULL AUTO_INCREMENT,
  `processo_id` int NOT NULL,
  `prazo_final` date NOT NULL,
  `justificativa_fornecedores` text,
  `condicoes_contratuais` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lotes_solicitacao`
--

LOCK TABLES `lotes_solicitacao` WRITE;
/*!40000 ALTER TABLE `lotes_solicitacao` DISABLE KEYS */;
INSERT INTO `lotes_solicitacao` VALUES
(1,1,'2025-06-30','Pertence ao ramo relacionado ao escopo da aquisição','Entrega em 15 dias após empenho'),
(2,3,'2025-07-03','Fornecedor pertence ao mercado do objeto.','ENTREGA EM 15 DIAS APÓS EMISSÃO DA NE'),
(3,6,'2025-07-15','Empresas especializadas no ramo gráfico.',NULL),
(4,7,'2025-08-01','Fornecedores de TI com boa reputação no mercado.',NULL),
(5,3,'2025-07-07','não se aplica','entrega imediata'),
(6,2,'2025-07-09','não se aplica','entrega imediata'),
(7,11,'2025-07-10','Empresa da atividade pretendida','Entrega imediata após emissão do Empenho'),
(8,12,'2025-07-10','Fornecedor faz parte do ramo da cotação','Entrega é imediata após o empenho'),
(9,12,'2025-07-10','Fornecedor faz parte do ramo da cotação','Entrega imediata após empenho'),
(10,13,'2025-07-10','Considerando se tratar de fornecedor do ramo','Entrega imediata após empenho'),
(11,14,'2025-07-11','Considerando se tratar de empresa do ramo','Entrega imediata após emissão do empenho'),
(12,15,'2025-07-19','Fornecedor do ramo da atividade','Entrega imediata após emissão de empenho'),
(13,16,'2025-08-17','Fornecedores do ramo da pesquisa','Prazo de entre em 15 dias após emissão da nota de empenho');
/*!40000 ALTER TABLE `lotes_solicitacao` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lotes_solicitacao_fornecedores`
--

DROP TABLE IF EXISTS `lotes_solicitacao_fornecedores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `lotes_solicitacao_fornecedores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `lote_solicitacao_id` int NOT NULL,
  `fornecedor_id` int NOT NULL,
  `token` varchar(64) NOT NULL,
  `status` enum('Enviado','Respondido','Expirado') DEFAULT 'Enviado',
  `data_envio` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `data_resposta` timestamp NULL DEFAULT NULL,
  `caminho_anexo` varchar(255) DEFAULT NULL,
  `nome_original_anexo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lotes_solicitacao_fornecedores`
--

LOCK TABLES `lotes_solicitacao_fornecedores` WRITE;
/*!40000 ALTER TABLE `lotes_solicitacao_fornecedores` DISABLE KEYS */;
INSERT INTO `lotes_solicitacao_fornecedores` VALUES
(1,1,1,'e2e5ab9fc6562e860ae5f11dd7a970ba6388c9d168b076d5207918eba3be1784','Respondido','2025-06-25 20:37:07','2025-06-25 20:46:28','550aa051f4635363e1b9ae628e85e7c1.pdf','cotacao material de escritorio ALFA.pdf'),
(2,2,1,'79cb0ebafbaf1aa2dcacf65afe996162542617de40471f05c6ec341655035250','Respondido','2025-06-28 22:28:20','2025-06-28 22:33:39','39b28d1670300c91a9062a82dfa81bce.pdf','Cotação teste.pdf'),
(3,0,0,'token1','Respondido','2025-06-25 10:00:00','2025-06-28 15:00:00',NULL,NULL),
(4,0,1,'token2','Enviado','2025-06-26 11:00:00',NULL,NULL,NULL),
(5,0,0,'token3','Enviado','2025-06-26 11:00:00',NULL,NULL,NULL),
(6,5,1,'054297970995a26ad0a6b2863db82d7f1b6b46a52fde2cb01c276677e7b280e8','Respondido','2025-07-02 22:53:49','2025-07-02 22:55:57','8953dde22512e62b7eb1b5867ed4fa51.pdf','teste michel.pdf'),
(7,6,1,'909e29901b0784f36df2d022cda2f65169dbaca64fcc3183ff3fa88c33171efd','Respondido','2025-07-04 23:33:37','2025-07-04 23:35:25','5da017fbb32fe7a351a9e92bbd2e03d1.pdf','proposta para caneta.pdf'),
(8,7,1,'f0b08031e1e86cd7ffdc160aab65291f65661665e9fb01869e6d8157ff9911a4','Respondido','2025-07-05 13:04:47','2025-07-05 13:07:33','5edac16c6d7be25ce77967d8dafced73.pdf','cotação material de expediente.pdf'),
(9,8,2,'fb54c274110a2fa66e5b179a81db59f4b01f9678e832d78a08c4afca0bcdab7d','Enviado','2025-07-05 14:34:50',NULL,NULL,NULL),
(10,9,1,'32c559e1fbb0c169e6a2aa57cc35a21d0a548d9268832573aaad3499e1d491ed','Respondido','2025-07-05 14:36:25','2025-07-05 14:38:22','cae3eea64b1eb37cb3880db292ac87d4.pdf','cotação 21.pdf'),
(11,10,1,'360907edbff384a9fcddc7ce999c032695ac32c67588fafe00fc64087e11108c','Respondido','2025-07-05 15:33:19','2025-07-05 15:35:09','f29a783e59174f9ac6a8dfc38c23a2da.pdf','cotação de papel.pdf'),
(12,11,1,'83304ba57b254fd39463d1442e36b633c0b594372bff93c53fd0d8d0606074f6','Respondido','2025-07-06 14:45:21','2025-07-06 14:47:22','b94e95b32b134c7bb0543796c304ecef.pdf','proposta papel teste.pdf'),
(13,12,1,'283cbb986cca72b5a86bb0dc1f27b0a8984c185b6cf0ddf89038672b8616e5ca','Respondido','2025-07-14 21:44:20','2025-07-14 21:47:43','5544e893eec4da227a84cbeeb91fb8e2.pdf','cotação para papel.pdf'),
(14,13,1,'7bfc22f375b818f3afc941f228e61d427757f6aca73222be8eb065fca3f5c7e5','Respondido','2025-08-12 12:34:59','2025-08-12 12:39:25','d22311365e2d21314f06d20d51c2c2b8.pdf','cotação papel A4.pdf'),
(15,13,2,'6bca9867fcc3e26424c4793ebf2d4e2e3f212ab1b241707add2d57d80192369e','Enviado','2025-08-12 12:34:59',NULL,NULL,NULL);
/*!40000 ALTER TABLE `lotes_solicitacao_fornecedores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lotes_solicitacao_itens`
--

DROP TABLE IF EXISTS `lotes_solicitacao_itens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `lotes_solicitacao_itens` (
  `lote_solicitacao_id` int NOT NULL,
  `item_id` int NOT NULL,
  PRIMARY KEY (`lote_solicitacao_id`,`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lotes_solicitacao_itens`
--

LOCK TABLES `lotes_solicitacao_itens` WRITE;
/*!40000 ALTER TABLE `lotes_solicitacao_itens` DISABLE KEYS */;
INSERT INTO `lotes_solicitacao_itens` VALUES
(1,1),
(1,2),
(1,3),
(1,4),
(1,5),
(2,7),
(2,8),
(2,9),
(5,7),
(5,8),
(5,9),
(6,6),
(7,17),
(7,18),
(7,19),
(7,20),
(8,29),
(8,30),
(8,31),
(8,32),
(9,29),
(9,30),
(9,31),
(9,32),
(10,35),
(11,40),
(12,41),
(13,42);
/*!40000 ALTER TABLE `lotes_solicitacao_itens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notas_tecnicas`
--

DROP TABLE IF EXISTS `notas_tecnicas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `notas_tecnicas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `numero_nota` int NOT NULL,
  `ano_nota` int NOT NULL,
  `processo_id` int DEFAULT NULL,
  `cotacao_rapida_id` int DEFAULT NULL,
  `tipo` enum('PROCESSO','COTACAO_RAPIDA') NOT NULL,
  `gerada_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `gerada_por` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notas_tecnicas`
--

LOCK TABLES `notas_tecnicas` WRITE;
/*!40000 ALTER TABLE `notas_tecnicas` DISABLE KEYS */;
INSERT INTO `notas_tecnicas` VALUES
(1,1,2025,2,NULL,'PROCESSO','2025-06-26 00:33:16','Joabe Oliveira'),
(2,2,2025,3,NULL,'PROCESSO','2025-06-28 22:49:18','Joabe Oliveira'),
(3,3,2025,3,NULL,'PROCESSO','2025-06-29 21:11:12','Joabe Oliveira'),
(4,4,2025,3,NULL,'PROCESSO','2025-07-02 23:00:03','Joabe Oliveira'),
(5,5,2025,NULL,1,'COTACAO_RAPIDA','2025-07-04 23:30:42','JUlio Alves'),
(6,6,2025,3,NULL,'PROCESSO','2025-07-04 23:32:15','Joabe Oliveira'),
(7,7,2025,11,NULL,'PROCESSO','2025-07-05 13:16:05','Joabe Oliveira'),
(8,8,2025,NULL,2,'COTACAO_RAPIDA','2025-07-05 13:37:49','Julio Alves'),
(9,9,2025,11,NULL,'PROCESSO','2025-07-05 13:44:20','Joabe Oliveira'),
(10,10,2025,12,NULL,'PROCESSO','2025-07-05 14:41:21','Ana Silva'),
(11,11,2025,NULL,3,'COTACAO_RAPIDA','2025-07-05 14:45:16','julio alves'),
(12,12,2025,12,NULL,'PROCESSO','2025-07-05 15:20:10','Ana Silva'),
(13,13,2025,13,NULL,'PROCESSO','2025-07-05 15:38:52','Joabe Oliveira'),
(14,14,2025,NULL,4,'COTACAO_RAPIDA','2025-07-05 15:42:14','julio alves'),
(15,15,2025,NULL,5,'COTACAO_RAPIDA','2025-07-05 19:45:22','julio alves'),
(16,16,2025,14,NULL,'PROCESSO','2025-07-06 14:52:58','Julio Alves'),
(17,17,2025,NULL,6,'COTACAO_RAPIDA','2025-07-06 14:57:27','julio alves'),
(18,18,2025,14,NULL,'PROCESSO','2025-07-08 10:41:21','Julio Alves'),
(19,19,2025,NULL,7,'COTACAO_RAPIDA','2025-07-14 21:50:59','julio alves'),
(20,20,2025,15,NULL,'PROCESSO','2025-07-14 21:59:31','Julio Alves'),
(21,21,2025,16,NULL,'PROCESSO','2025-08-12 12:52:26','Joabe Oliveira'),
(22,22,2025,NULL,8,'COTACAO_RAPIDA','2025-08-12 12:58:11','julio alves');
/*!40000 ALTER TABLE `notas_tecnicas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `precos_coletados`
--

DROP TABLE IF EXISTS `precos_coletados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `precos_coletados` (
  `id` int NOT NULL AUTO_INCREMENT,
  `item_id` int NOT NULL,
  `fonte` varchar(255) NOT NULL,
  `valor` decimal(15,2) NOT NULL,
  `unidade_medida` varchar(50) DEFAULT NULL,
  `data_coleta` date NOT NULL,
  `fornecedor_nome` varchar(255) DEFAULT NULL,
  `fornecedor_cnpj` varchar(18) DEFAULT NULL,
  `link_evidencia` text,
  `status_analise` enum('considerado','desconsiderado') DEFAULT 'considerado',
  `justificativa_descarte` text,
  `criado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=185 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `precos_coletados`
--

LOCK TABLES `precos_coletados` WRITE;
/*!40000 ALTER TABLE `precos_coletados` DISABLE KEYS */;
INSERT INTO `precos_coletados` VALUES
(1,1,'Pesquisa com Fornecedor',1.20,'UN','2025-06-25','Alfa Suprimentos e Comércio EIRELI','11.000.111/0001-57',NULL,'considerado',NULL,'2025-06-25 20:46:28'),
(2,2,'Pesquisa com Fornecedor',1.20,'UN','2025-06-25','Alfa Suprimentos e Comércio EIRELI','11.000.111/0001-57',NULL,'considerado',NULL,'2025-06-25 20:46:28'),
(3,3,'Pesquisa com Fornecedor',1.20,'UN','2025-06-25','Alfa Suprimentos e Comércio EIRELI','11.000.111/0001-57',NULL,'considerado',NULL,'2025-06-25 20:46:28'),
(4,4,'Pesquisa com Fornecedor',0.55,'FL','2025-06-25','Alfa Suprimentos e Comércio EIRELI','11.000.111/0001-57',NULL,'considerado',NULL,'2025-06-25 20:46:28'),
(5,5,'Pesquisa com Fornecedor',0.80,'UN','2025-06-25','Alfa Suprimentos e Comércio EIRELI','11.000.111/0001-57',NULL,'considerado',NULL,'2025-06-25 20:46:28'),
(6,1,'Painel de Preços',0.65,'UN','2025-05-27','F C COMERCIO DE MATERIAIS DE LIMPEZA LTDA','50344473000184',NULL,'considerado',NULL,'2025-06-25 20:49:19'),
(7,1,'Painel de Preços',0.47,'UN','2025-04-11','GALERIA DA VILA ADMINISTRACAO DE BENS E SERVICOS LTDA','31431308000115',NULL,'considerado',NULL,'2025-06-25 20:49:19'),
(8,1,'Painel de Preços',0.56,'UN','2024-12-10','J G MARQUES','40815897000126',NULL,'considerado',NULL,'2025-06-25 20:49:19'),
(9,1,'Contratação Similar',0.60,'UN','2024-11-22','PORTA HELICOPTEROS MULTIPROPóSITO\"ATLâNTICO\"','56187319000122',NULL,'considerado',NULL,'2025-06-25 20:50:44'),
(10,1,'Contratação Similar',0.68,'UN','2024-10-18','MEC-FACULDADE DE MEDICINA DA UF/RJ','51222903000158',NULL,'considerado',NULL,'2025-06-25 20:50:44'),
(11,1,'Contratação Similar',0.60,'UN','2024-10-16','CAIXA DE CONSTRUCÕES DE CASAS P/PESSOAL DA M','17526067000167',NULL,'considerado',NULL,'2025-06-25 20:50:44'),
(12,1,'Site Especializado',1.30,'UN','2025-06-25','Tuti Papelaria',NULL,'https://www.tutipapelaria.com.br/escrita/caneta/esferografica/caneta-esferografica-dura-azul-bic?gad_source=1&gad_campaignid=22423069657&gbraid=0AAAAAqK0X0anoM0rWdA2yBWw8JwuKK8A8&gclid=CjwKCAjwvO7CBhAqEiwA9q2YJcY7oQUcdjnC7C_R0eA7z2vm5HrSkN1zA_gp6rSz8Iv93nGVKL0UqhoCw7UQAvD_BwE','considerado',NULL,'2025-06-25 20:55:52'),
(13,2,'Painel de Preços',0.55,'UN','2025-06-05','ADL PRODUTOS E SERVICOS LTDA','31788699000120',NULL,'considerado',NULL,'2025-06-25 20:57:04'),
(14,2,'Painel de Preços',0.47,'UN','2025-05-16','RCE ARTIGOS DE PAPELARIA LTDA','49042895000116',NULL,'considerado',NULL,'2025-06-25 20:57:04'),
(15,2,'Painel de Preços',0.47,'UN','2025-05-16','RCE ARTIGOS DE PAPELARIA LTDA','49042895000116',NULL,'considerado',NULL,'2025-06-25 20:57:04'),
(16,2,'Contratação Similar',0.62,'UN','2024-08-06','CASA DA MOEDA DO BRASIL/MF','18631695000175',NULL,'considerado',NULL,'2025-06-25 20:57:19'),
(17,2,'Contratação Similar',0.74,'UN','2024-05-10','CONSELHO REG.DOS REPRESENTANTES COMERCIAIS-RJ','48760218000170',NULL,'considerado',NULL,'2025-06-25 20:57:19'),
(18,2,'Contratação Similar',1.50,'UN','2023-11-30','ODONTOCLINICA CENTRAL','29956966000189',NULL,'considerado',NULL,'2025-06-25 20:57:19'),
(19,2,'Site Especializado',1.30,'UN','2025-06-25','Tuti Papelaria',NULL,'https://www.tutipapelaria.com.br/escrita/caneta/esferografica/caneta-esferografica-dura-vermelho-bic','considerado',NULL,'2025-06-25 20:58:09'),
(20,3,'Painel de Preços',0.55,'UN','2025-06-05','ADL PRODUTOS E SERVICOS LTDA','31788699000120',NULL,'considerado',NULL,'2025-06-25 20:58:53'),
(21,3,'Painel de Preços',0.50,'UN','2025-05-13','ALFA PAPELARIA LTDA','37878675000148',NULL,'considerado',NULL,'2025-06-25 20:58:53'),
(22,3,'Painel de Preços',0.55,'UN','2025-05-07','COMERCIAL TERRA LTDA','08659585000168',NULL,'considerado',NULL,'2025-06-25 20:58:53'),
(23,3,'Contratação Similar',0.60,'UN','2024-06-19','CENTRO MISSEIS E AR.SUBMAR.ALM.LUIZ A.P.NEVES','45175426000114',NULL,'considerado',NULL,'2025-06-25 20:59:05'),
(24,3,'Contratação Similar',0.74,'UN','2024-05-10','CONSELHO REG.DOS REPRESENTANTES COMERCIAIS-RJ','48760218000170',NULL,'considerado',NULL,'2025-06-25 20:59:05'),
(25,3,'Contratação Similar',0.56,'UN','2023-08-24','CASA DA MOEDA DO BRASIL/MF','39700820000121',NULL,'considerado',NULL,'2025-06-25 20:59:05'),
(26,3,'Site Especializado',1.30,'UN','2025-06-25','Tuti Papelaria',NULL,'https://www.tutipapelaria.com.br/escrita/caneta/esferografica/caneta-esferografica-dura-vermelho-bic','considerado',NULL,'2025-06-25 20:59:41'),
(27,4,'Painel de Preços',19.97,'EMB c/ 500 FL','2025-06-24','MMC SERVICOS DIVERSOS LTDA','54349003000164',NULL,'considerado',NULL,'2025-06-25 21:09:38'),
(28,4,'Painel de Preços',19.95,'EMB c/ 500 FL','2025-06-23','RD PAPEIS & EPI LTDA','08822824000159',NULL,'considerado',NULL,'2025-06-25 21:09:38'),
(29,4,'Painel de Preços',20.50,'EMB c/ 500 FL','2025-06-18','RYMO-IMAGEM E PRODUTOS GRAFICOS DA AMAZONIA LTDA','14220230000170',NULL,'considerado',NULL,'2025-06-25 21:09:38'),
(30,4,'Contratação Similar',22.00,'EMB c/ 500 FL','2025-06-16','CAMARA MUNICIPAL DE PARACAMBI - RJ','26610304000164',NULL,'considerado',NULL,'2025-06-25 21:10:42'),
(31,4,'Contratação Similar',20.72,'EMB c/ 500 FL','2025-06-05','PREFEITURA MUNICIPAL DE BARRA DO PIRAI - RJ','08198623000203',NULL,'considerado',NULL,'2025-06-25 21:10:42'),
(32,4,'Contratação Similar',20.33,'EMB c/ 500 FL','2024-11-11','SUPERINTENDENCIA ESTADUAL DO MS/RJ','52571752000106',NULL,'considerado',NULL,'2025-06-25 21:10:42'),
(33,4,'Site Especializado',29.00,'EMB c/ 500 FL','2025-06-25','Tuti Papelaria',NULL,'https://www.tutipapelaria.com.br/escrita/papel/papel-a4-report-premium-75g-500-folhas-suzano','considerado',NULL,'2025-06-25 21:12:18'),
(34,5,'Painel de Preços',0.34,'UN','2025-03-19','STAR MIX COMERCIO DE PAPELARIA E VARIEDADES LTDA','56385366000180',NULL,'considerado',NULL,'2025-06-25 21:13:02'),
(35,5,'Painel de Preços',0.38,'UN','2025-03-14','BIANCA RICACHESKI RAUBER','28584842000238',NULL,'considerado',NULL,'2025-06-25 21:13:02'),
(36,5,'Painel de Preços',0.36,'UN','2025-03-14','33.622.151 ISABEL ALVES DE SOUZA','33622151000130',NULL,'considerado',NULL,'2025-06-25 21:13:02'),
(37,5,'Contratação Similar',0.50,'UN','2024-12-04','INST.FED.DE EDUC.,CIENC.E TEC.FLUMINENSE','12763920000140',NULL,'considerado',NULL,'2025-06-25 21:13:24'),
(38,5,'Contratação Similar',0.49,'UN','2024-08-16','DIRETORIA DE ABASTECIMENTO DA MARINHA','15787817000129',NULL,'considerado',NULL,'2025-06-25 21:13:24'),
(39,5,'Contratação Similar',0.61,'UN','2024-07-03','CONSELHO REGIONAL DE ODONTOLOGIA - RJ','17526067000167',NULL,'considerado',NULL,'2025-06-25 21:13:24'),
(40,5,'Site Especializado',0.69,'UN','2025-06-25','Gimba',NULL,'https://www.gimba.com.br/lapis-preto-sextavado/lapis-preto-hb-n2-eco-multicolor-1-un-faber-castell/?PID=717','considerado',NULL,'2025-06-25 21:16:22'),
(41,6,'Painel de Preços',0.53,'UN','2025-06-12','DAGEAL - COMERCIO DE MATERIAL DE ESCRITORIO LTDA','07245458000150',NULL,'considerado',NULL,'2025-06-25 23:49:50'),
(42,6,'Painel de Preços',0.99,'UN','2024-07-15','NEXUS COMERCIO DE MATERIAL DE EXPEDIENTE LTDA','55322902000136',NULL,'considerado',NULL,'2025-06-25 23:49:50'),
(43,6,'Painel de Preços',0.88,'UN','2023-07-12','DESIDERATI INTERMEDIACAO COMERCIAL LTDA','45727448000140',NULL,'considerado',NULL,'2025-06-25 23:49:50'),
(44,6,'Contratação Similar',0.99,'UN','2024-07-15','FUNDACAO NACIONAL DE ARTES','55322902000136',NULL,'considerado',NULL,'2025-06-25 23:49:59'),
(45,6,'Contratação Similar',0.88,'UN','2023-07-12','PROCURADORIA REGIONAL DA REPUBLICA-2A.REGIÃO','45727448000140',NULL,'considerado',NULL,'2025-06-25 23:49:59'),
(46,6,'Site Especializado',1.48,'UN','2025-06-25','Casa do Roadie',NULL,'https://www.casadoroadie.com.br/caneta-esferografica-0-8mm-bic-azul','considerado',NULL,'2025-06-25 23:51:13'),
(47,7,'Pesquisa com Fornecedor',1.50,'UNIDADE','2025-06-28','Alfa Suprimentos e Comércio EIRELI','11.000.111/0001-57',NULL,'considerado',NULL,'2025-06-28 22:33:39'),
(48,8,'Pesquisa com Fornecedor',29.00,'EMB c/ 500 FL','2025-06-28','Alfa Suprimentos e Comércio EIRELI','11.000.111/0001-57',NULL,'considerado',NULL,'2025-06-28 22:33:39'),
(49,9,'Pesquisa com Fornecedor',1.50,'UNIDADE','2025-06-28','Alfa Suprimentos e Comércio EIRELI','11.000.111/0001-57',NULL,'considerado',NULL,'2025-06-28 22:33:39'),
(50,7,'Painel de Preços',0.53,'UN','2025-06-12','DAGEAL - COMERCIO DE MATERIAL DE ESCRITORIO LTDA','07245458000150',NULL,'considerado',NULL,'2025-06-28 22:36:13'),
(51,7,'Painel de Preços',0.77,'UN','2024-11-18','29.604.467 ROSENILTON OLIVEIRA DE LIMA','29604467000122',NULL,'considerado',NULL,'2025-06-28 22:36:13'),
(52,7,'Painel de Preços',0.99,'UN','2024-07-15','NEXUS COMERCIO DE MATERIAL DE EXPEDIENTE LTDA','55322902000136',NULL,'considerado',NULL,'2025-06-28 22:36:13'),
(53,7,'Contratação Similar',0.99,'UN','2024-07-15','FUNDACAO NACIONAL DE ARTES','55322902000136',NULL,'considerado',NULL,'2025-06-28 22:37:30'),
(54,7,'Contratação Similar',0.88,'UN','2023-07-12','PROCURADORIA REGIONAL DA REPUBLICA-2A.REGIÃO','45727448000140',NULL,'considerado',NULL,'2025-06-28 22:37:30'),
(55,7,'Site Especializado',1.10,'UN','2025-06-28','Magazine luiza',NULL,'https://www.magazineluiza.com.br/caneta-esferografica-jocar-office-simple-1-0mm-azul-generico/p/hahj79k125/pa/cesf/?seller_id=qualitechtecnologia&srsltid=AfmBOoqmBtsopV--hAA3lueRcEKU66Q5_4_oVymw1GOU3-4dc8UQTYcZsr0','considerado',NULL,'2025-06-28 22:40:29'),
(56,8,'Painel de Preços',23.47,'EMB c/ 500 FL','2025-06-27','ESKIP DISTRIBUIDORA LTDA','47128762000131',NULL,'considerado',NULL,'2025-06-28 22:41:20'),
(57,8,'Painel de Preços',20.70,'EMB c/ 500 FL','2025-06-25','RD PAPEIS & EPI LTDA','08822824000159',NULL,'considerado',NULL,'2025-06-28 22:41:20'),
(58,8,'Painel de Preços',19.90,'EMB c/ 500 FL','2025-06-25','RD PAPEIS & EPI LTDA','08822824000159',NULL,'considerado',NULL,'2025-06-28 22:41:20'),
(59,8,'Contratação Similar',21.25,'EMB c/ 500 FL','2025-04-17','INSTITUTO OSWALDO CRUZ','45404628000190',NULL,'considerado',NULL,'2025-06-28 22:41:48'),
(60,8,'Contratação Similar',23.80,'EMB c/ 500 FL','2025-02-07','HOSPITAL UNIVERSITÁRIO GAFFRÉE E GUINLE','57395625000117',NULL,'considerado',NULL,'2025-06-28 22:41:48'),
(61,8,'Contratação Similar',19.54,'EMB c/ 500 FL','2024-11-11','SUPERINTENDENCIA ESTADUAL DO MS/RJ','08198623000203',NULL,'considerado',NULL,'2025-06-28 22:41:48'),
(62,8,'Site Especializado',25.00,'EMB c/ 500 FL','2025-06-28','Magazine luiza',NULL,'https://www.magazineluiza.com.br/papel-sulfite-a4-c-500fls-75g-chamex/p/eke84c8j7h/pa/pslt/?seller_id=papelariasaojorge&srsltid=AfmBOorfD05dDYZ2eIb3nturka5XOYtRXO9TnReQnBekDxA3L_VGQ-BipzI','considerado',NULL,'2025-06-28 22:43:12'),
(63,9,'Painel de Preços',1.01,'UN','2025-06-24','INDUSTRIA FENIX CORTE A LASER LTDA','13759849000195',NULL,'considerado',NULL,'2025-06-28 22:43:40'),
(64,9,'Painel de Preços',1.10,'UN','2023-12-06','ALNETTO COMERCIAL E SERVICOS LTDA','27039914000112',NULL,'considerado',NULL,'2025-06-28 22:43:40'),
(65,9,'Painel de Preços',1.00,'UN','2022-04-12','R D DAVID - PRODUTOS PROMOCIONAIS','15221634000141',NULL,'considerado',NULL,'2025-06-28 22:43:40'),
(66,9,'Contratação Similar',1.10,'UN','2023-12-06','PREFEITURA MUNICIPAL DO RIO DE JANEIRO - RJ','27039914000112',NULL,'considerado',NULL,'2025-06-28 22:43:50'),
(67,9,'Contratação Similar',0.80,'','2022-02-23','INSTITUTO DE TECNOLOGIA EM IMUNOBIOLOGICOS','17526067000167',NULL,'considerado',NULL,'2025-06-28 22:43:50'),
(68,9,'Site Especializado',2.00,'UN','2025-06-28','Ideia Papelaria',NULL,'https://www.ideiapapelaria.com.br/produtos/regua-20cm-plastica-waleu/?srsltid=AfmBOoqvgrWlwyNSWKt6AKhpU5NGDMsxc2QcIuUMZI0tdeDshN5sgbrtsGg','considerado',NULL,'2025-06-28 22:45:56'),
(69,0,'Painel de Preços',4200.00,NULL,'2025-03-10','Órgão X',NULL,NULL,'considerado',NULL,'2025-06-30 00:58:25'),
(70,0,'Pesquisa com Fornecedor',4250.50,NULL,'2025-03-11','Alfa Suprimentos & TI',NULL,NULL,'considerado',NULL,'2025-06-30 00:58:25'),
(71,0,'Contratação Similar',4300.00,NULL,'2025-03-12','Órgão Y',NULL,NULL,'considerado',NULL,'2025-06-30 00:58:25'),
(72,0,'Site Especializado',5500.00,NULL,'2025-03-12','Loja Z',NULL,NULL,'desconsiderado',NULL,'2025-06-30 00:58:25'),
(73,11,'Painel de Preços',21.50,NULL,'2025-04-15','Órgão A',NULL,NULL,'considerado',NULL,'2025-06-30 00:58:25'),
(74,11,'Pesquisa com Fornecedor',22.80,NULL,'2025-04-16','Beta Comércio de Limpeza',NULL,NULL,'considerado',NULL,'2025-06-30 00:58:25'),
(75,11,'Pesquisa com Fornecedor',24.00,NULL,'2025-04-16','Outro Fornecedor Limpeza',NULL,NULL,'considerado',NULL,'2025-06-30 00:58:25'),
(76,12,'Pesquisa com Fornecedor',15.40,NULL,'2025-04-16','Beta Comércio de Limpeza',NULL,NULL,'considerado',NULL,'2025-06-30 00:58:25'),
(77,12,'Contratação Similar',16.00,NULL,'2025-04-18','Órgão B',NULL,NULL,'considerado',NULL,'2025-06-30 00:58:25'),
(78,16,'Painel de Preços',38.00,NULL,'2025-05-20','Órgão C',NULL,NULL,'considerado',NULL,'2025-06-30 00:58:25'),
(79,16,'Pesquisa com Fornecedor',35.75,NULL,'2025-05-21','Beta Comércio de Limpeza',NULL,NULL,'considerado',NULL,'2025-06-30 00:58:25'),
(80,16,'Site Especializado',36.50,NULL,'2025-05-22','Supermercado Online',NULL,NULL,'considerado',NULL,'2025-06-30 00:58:25'),
(81,0,'Painel de Preços',4200.00,NULL,'2025-03-10','Órgão X',NULL,NULL,'considerado',NULL,'2025-06-30 00:58:25'),
(82,0,'Pesquisa com Fornecedor',4250.50,NULL,'2025-03-11','Alfa Suprimentos & TI',NULL,NULL,'considerado',NULL,'2025-06-30 00:58:25'),
(83,0,'Contratação Similar',4300.00,NULL,'2025-03-12','Órgão Y',NULL,NULL,'considerado',NULL,'2025-06-30 00:58:25'),
(84,0,'Site Especializado',5500.00,NULL,'2025-03-12','Loja Z',NULL,NULL,'desconsiderado',NULL,'2025-06-30 00:58:25'),
(85,11,'Painel de Preços',21.50,NULL,'2025-04-15','Órgão A',NULL,NULL,'considerado',NULL,'2025-06-30 00:58:25'),
(86,11,'Pesquisa com Fornecedor',22.80,NULL,'2025-04-16','Beta Comércio de Limpeza',NULL,NULL,'considerado',NULL,'2025-06-30 00:58:25'),
(87,11,'Pesquisa com Fornecedor',24.00,NULL,'2025-04-16','Outro Fornecedor Limpeza',NULL,NULL,'considerado',NULL,'2025-06-30 00:58:25'),
(88,12,'Pesquisa com Fornecedor',15.40,NULL,'2025-04-16','Beta Comércio de Limpeza',NULL,NULL,'considerado',NULL,'2025-06-30 00:58:25'),
(89,12,'Contratação Similar',16.00,NULL,'2025-04-18','Órgão B',NULL,NULL,'considerado',NULL,'2025-06-30 00:58:25'),
(90,16,'Painel de Preços',38.00,NULL,'2025-05-20','Órgão C',NULL,NULL,'considerado',NULL,'2025-06-30 00:58:25'),
(91,16,'Pesquisa com Fornecedor',35.75,NULL,'2025-05-21','Beta Comércio de Limpeza',NULL,NULL,'considerado',NULL,'2025-06-30 00:58:25'),
(92,16,'Site Especializado',36.50,NULL,'2025-05-22','Supermercado Online',NULL,NULL,'considerado',NULL,'2025-06-30 00:58:25'),
(93,7,'Pesquisa com Fornecedor',1.50,'UNIDADE','2025-07-02','Alfa Suprimentos e Comércio EIRELI','11.000.111/0001-57',NULL,'considerado',NULL,'2025-07-02 22:55:57'),
(94,8,'Pesquisa com Fornecedor',29.00,'EMB c/ 500 FL','2025-07-02','Alfa Suprimentos e Comércio EIRELI','11.000.111/0001-57',NULL,'considerado',NULL,'2025-07-02 22:55:57'),
(95,9,'Pesquisa com Fornecedor',2.00,'UNIDADE','2025-07-02','Alfa Suprimentos e Comércio EIRELI','11.000.111/0001-57',NULL,'considerado',NULL,'2025-07-02 22:55:57'),
(96,7,'Painel de Preços',0.53,'UN','2025-06-12','DAGEAL - COMERCIO DE MATERIAL DE ESCRITORIO LTDA','07245458000150',NULL,'considerado',NULL,'2025-07-02 22:57:17'),
(97,7,'Painel de Preços',0.77,'UN','2024-11-18','29.604.467 ROSENILTON OLIVEIRA DE LIMA','29604467000122',NULL,'considerado',NULL,'2025-07-02 22:57:17'),
(98,7,'Painel de Preços',0.99,'UN','2024-07-15','NEXUS COMERCIO DE MATERIAL DE EXPEDIENTE LTDA','55322902000136',NULL,'considerado',NULL,'2025-07-02 22:57:17'),
(99,6,'Pesquisa com Fornecedor',1.50,'UN','2025-07-04','Alfa Suprimentos e Comércio EIRELI','11.000.111/0001-57',NULL,'considerado',NULL,'2025-07-04 23:35:25'),
(100,17,'Pesquisa com Fornecedor',1.80,'UN','2025-07-05','Alfa Suprimentos e Comércio EIRELI','11.000.111/0001-57',NULL,'considerado',NULL,'2025-07-05 13:07:33'),
(101,18,'Pesquisa com Fornecedor',1.80,'UN','2025-07-05','Alfa Suprimentos e Comércio EIRELI','11.000.111/0001-57',NULL,'considerado',NULL,'2025-07-05 13:07:33'),
(102,19,'Pesquisa com Fornecedor',0.15,'FL','2025-07-05','Alfa Suprimentos e Comércio EIRELI','11.000.111/0001-57',NULL,'considerado',NULL,'2025-07-05 13:07:33'),
(103,20,'Pesquisa com Fornecedor',1.50,'UN','2025-07-05','Alfa Suprimentos e Comércio EIRELI','11.000.111/0001-57',NULL,'considerado',NULL,'2025-07-05 13:07:33'),
(104,17,'Painel de Preços',1.69,'UN','2025-06-25','R DE AMORIM COMERCIO & SERVICO LTDA','53874150000190',NULL,'considerado',NULL,'2025-07-05 13:09:51'),
(105,17,'Painel de Preços',0.65,'UN','2025-05-27','F C COMERCIO DE MATERIAIS DE LIMPEZA LTDA','50344473000184',NULL,'considerado',NULL,'2025-07-05 13:09:51'),
(106,17,'Painel de Preços',0.47,'UN','2025-04-11','GALERIA DA VILA ADMINISTRACAO DE BENS E SERVICOS LTDA','31431308000115',NULL,'considerado',NULL,'2025-07-05 13:09:51'),
(107,17,'Contratação Similar',0.40,'UN','2024-11-22','PORTA HELICOPTEROS MULTIPROPóSITO\"ATLâNTICO\"','53350452000160',NULL,'considerado',NULL,'2025-07-05 13:11:36'),
(108,17,'Contratação Similar',0.60,'UN','2024-11-22','PORTA HELICOPTEROS MULTIPROPóSITO\"ATLâNTICO\"','56187319000122',NULL,'considerado',NULL,'2025-07-05 13:11:36'),
(109,17,'Contratação Similar',2.20,'UN','2024-10-29','BASE DE FUZILEIROS NAVAIS DO RIO MERITI','35967965000132',NULL,'considerado',NULL,'2025-07-05 13:11:36'),
(110,18,'Painel de Preços',1.83,'UN','2025-06-26','PUBLIAT LTDA','49814995000113',NULL,'considerado',NULL,'2025-07-05 13:39:59'),
(111,18,'Painel de Preços',0.55,'UN','2025-06-05','ADL PRODUTOS E SERVICOS LTDA','31788699000120',NULL,'considerado',NULL,'2025-07-05 13:39:59'),
(112,18,'Painel de Preços',0.47,'UN','2025-05-16','RCE ARTIGOS DE PAPELARIA LTDA','49042895000116',NULL,'considerado',NULL,'2025-07-05 13:39:59'),
(113,18,'Contratação Similar',0.62,'UN','2024-08-06','CASA DA MOEDA DO BRASIL/MF','18631695000175',NULL,'considerado',NULL,'2025-07-05 13:40:28'),
(114,18,'Contratação Similar',0.74,'UN','2024-05-10','CONSELHO REG DOS REPRESENTANTES COMERCIAIS/RJ','48760218000170',NULL,'considerado',NULL,'2025-07-05 13:40:28'),
(115,18,'Contratação Similar',1.50,'UN','2023-11-30','ODONTOCLINICA CENTRAL','29956966000189',NULL,'considerado',NULL,'2025-07-05 13:40:28'),
(116,19,'Painel de Preços',18.79,'EMB c/ 500 FL','2025-07-01','58.037.027 VINICIUS GABRIEL PEREIRA DA SILVA','58037027000139',NULL,'considerado',NULL,'2025-07-05 13:41:00'),
(117,19,'Painel de Preços',19.77,'EMB c/ 500 FL','2025-06-30','MMC SERVICOS DIVERSOS LTDA','54349003000164',NULL,'considerado',NULL,'2025-07-05 13:41:00'),
(118,19,'Painel de Preços',25.00,'EMB c/ 500 FL','2025-06-30','54.619.660 HELIAB GEORGE COUTINHO RUFINO','54619660000184',NULL,'considerado',NULL,'2025-07-05 13:41:00'),
(119,19,'Contratação Similar',22.00,'EMB c/ 500 FL','2025-06-16','CAMARA MUNICIPAL DE PARACAMBI - RJ','26610304000164',NULL,'considerado',NULL,'2025-07-05 13:41:11'),
(120,19,'Contratação Similar',25.10,'EMB c/ 500 FL','2025-06-05','PREFEITURA MUNICIPAL DE BARRA DO PIRAI - RJ','53007570000170',NULL,'considerado',NULL,'2025-07-05 13:41:11'),
(121,19,'Contratação Similar',20.72,'EMB c/ 500 FL','2025-06-05','PREFEITURA MUNICIPAL DE BARRA DO PIRAI - RJ','08198623000203',NULL,'considerado',NULL,'2025-07-05 13:41:11'),
(122,20,'Painel de Preços',0.50,'UN','2025-06-02','LUPIAN ATACADO E VAREJO LTDA','55808167000175',NULL,'considerado',NULL,'2025-07-05 13:41:57'),
(123,20,'Painel de Preços',0.76,'UN','2025-03-12','GLB DISTRIBUIDORA LTDA','57594207000159',NULL,'considerado',NULL,'2025-07-05 13:41:57'),
(124,20,'Painel de Preços',0.76,'UN','2025-02-26','CAZ COMERCIO DE ARTIGOS PARA ESCRITORIO LTDA','47944342000123',NULL,'considerado',NULL,'2025-07-05 13:41:57'),
(125,20,'Contratação Similar',0.50,'UN','2024-12-04','INST.FED.DE EDUC.,CIENC.E TEC.FLUMINENSE','12763920000140',NULL,'considerado',NULL,'2025-07-05 13:42:12'),
(126,20,'Contratação Similar',2.31,'UN','2024-09-25','INST.FED.DE EDUC.,CIENC.E TEC.FLUMINENSE','56043196000156',NULL,'considerado',NULL,'2025-07-05 13:42:12'),
(127,20,'Contratação Similar',0.49,'UN','2024-08-16','DIRETORIA DE ABASTECIMENTO DA MARINHA','15787817000129',NULL,'considerado',NULL,'2025-07-05 13:42:12'),
(128,29,'Painel de Preços',1.69,'UN','2025-06-25','R DE AMORIM COMERCIO & SERVICO LTDA','53874150000190',NULL,'considerado',NULL,'2025-07-05 14:30:06'),
(129,29,'Painel de Preços',0.65,'UN','2025-05-27','F C COMERCIO DE MATERIAIS DE LIMPEZA LTDA','50344473000184',NULL,'considerado',NULL,'2025-07-05 14:30:06'),
(130,29,'Painel de Preços',0.47,'UN','2025-04-11','GALERIA DA VILA ADMINISTRACAO DE BENS E SERVICOS LTDA','31431308000115',NULL,'considerado',NULL,'2025-07-05 14:30:06'),
(131,29,'Contratação Similar',0.40,'UN','2024-11-22','PORTA HELICOPTEROS MULTIPROPóSITO\"ATLâNTICO\"','53350452000160',NULL,'considerado',NULL,'2025-07-05 14:30:26'),
(132,29,'Contratação Similar',0.60,'UN','2024-11-22','PORTA HELICOPTEROS MULTIPROPóSITO\"ATLâNTICO\"','56187319000122',NULL,'considerado',NULL,'2025-07-05 14:30:26'),
(133,29,'Contratação Similar',2.20,'UN','2024-10-29','BASE DE FUZILEIROS NAVAIS DO RIO MERITI','35967965000132',NULL,'considerado',NULL,'2025-07-05 14:30:26'),
(134,30,'Painel de Preços',1.83,'UN','2025-06-26','PUBLIAT LTDA','49814995000113',NULL,'considerado',NULL,'2025-07-05 14:31:12'),
(135,30,'Painel de Preços',0.55,'UN','2025-06-05','ADL PRODUTOS E SERVICOS LTDA','31788699000120',NULL,'considerado',NULL,'2025-07-05 14:31:12'),
(136,30,'Painel de Preços',0.47,'UN','2025-05-16','RCE ARTIGOS DE PAPELARIA LTDA','49042895000116',NULL,'considerado',NULL,'2025-07-05 14:31:12'),
(137,30,'Contratação Similar',0.62,'UN','2024-08-06','CASA DA MOEDA DO BRASIL/MF','18631695000175',NULL,'considerado',NULL,'2025-07-05 14:31:22'),
(138,30,'Contratação Similar',0.74,'UN','2024-05-10','CONSELHO REG DOS REPRESENTANTES COMERCIAIS/RJ','48760218000170',NULL,'considerado',NULL,'2025-07-05 14:31:22'),
(139,30,'Contratação Similar',1.50,'UN','2023-11-30','ODONTOCLINICA CENTRAL','29956966000189',NULL,'considerado',NULL,'2025-07-05 14:31:22'),
(140,31,'Painel de Preços',18.79,'EMB c/ 500 FL','2025-07-01','58.037.027 VINICIUS GABRIEL PEREIRA DA SILVA','58037027000139',NULL,'considerado',NULL,'2025-07-05 14:31:46'),
(141,31,'Painel de Preços',19.77,'EMB c/ 500 FL','2025-06-30','MMC SERVICOS DIVERSOS LTDA','54349003000164',NULL,'considerado',NULL,'2025-07-05 14:31:46'),
(142,31,'Painel de Preços',25.00,'EMB c/ 500 FL','2025-06-30','54.619.660 HELIAB GEORGE COUTINHO RUFINO','54619660000184',NULL,'considerado',NULL,'2025-07-05 14:31:46'),
(143,31,'Contratação Similar',22.00,'EMB c/ 500 FL','2025-06-16','CAMARA MUNICIPAL DE PARACAMBI - RJ','26610304000164',NULL,'considerado',NULL,'2025-07-05 14:31:58'),
(144,31,'Contratação Similar',25.10,'EMB c/ 500 FL','2025-06-05','PREFEITURA MUNICIPAL DE BARRA DO PIRAI - RJ','53007570000170',NULL,'considerado',NULL,'2025-07-05 14:31:58'),
(145,31,'Contratação Similar',20.72,'EMB c/ 500 FL','2025-06-05','PREFEITURA MUNICIPAL DE BARRA DO PIRAI - RJ','08198623000203',NULL,'considerado',NULL,'2025-07-05 14:31:58'),
(146,32,'Painel de Preços',2.35,'UN','2025-06-18','CAZ COMERCIO DE ARTIGOS PARA ESCRITORIO LTDA','47944342000123',NULL,'considerado',NULL,'2025-07-05 14:32:22'),
(147,32,'Painel de Preços',0.50,'UN','2025-06-02','LUPIAN ATACADO E VAREJO LTDA','55808167000175',NULL,'considerado',NULL,'2025-07-05 14:32:22'),
(148,32,'Painel de Preços',0.49,'UN','2025-04-15','DYFAL COMERCIO DE VARIEDADES B2G LTDA','48760218000170',NULL,'considerado',NULL,'2025-07-05 14:32:22'),
(149,32,'Contratação Similar',0.50,'UN','2024-12-04','INST.FED.DE EDUC.,CIENC.E TEC.FLUMINENSE','12763920000140',NULL,'considerado',NULL,'2025-07-05 14:32:36'),
(150,32,'Contratação Similar',2.31,'UN','2024-09-25','INST.FED.DE EDUC.,CIENC.E TEC.FLUMINENSE','56043196000156',NULL,'considerado',NULL,'2025-07-05 14:32:36'),
(151,32,'Contratação Similar',0.49,'UN','2024-08-16','DIRETORIA DE ABASTECIMENTO DA MARINHA','15787817000129',NULL,'considerado',NULL,'2025-07-05 14:32:36'),
(152,29,'Pesquisa com Fornecedor',1.80,'UN','2025-07-05','Alfa Suprimentos e Comércio EIRELI','11.000.111/0001-57',NULL,'considerado',NULL,'2025-07-05 14:38:22'),
(153,30,'Pesquisa com Fornecedor',1.80,'UN','2025-07-05','Alfa Suprimentos e Comércio EIRELI','11.000.111/0001-57',NULL,'considerado',NULL,'2025-07-05 14:38:22'),
(154,31,'Pesquisa com Fornecedor',22.50,'FL','2025-07-05','Alfa Suprimentos e Comércio EIRELI','11.000.111/0001-57',NULL,'considerado',NULL,'2025-07-05 14:38:22'),
(155,32,'Pesquisa com Fornecedor',1.20,'UN','2025-07-05','Alfa Suprimentos e Comércio EIRELI','11.000.111/0001-57',NULL,'considerado',NULL,'2025-07-05 14:38:22'),
(156,35,'Pesquisa com Fornecedor',28.00,'EMB c/ 500 FL','2025-07-05','Alfa Suprimentos e Comércio EIRELI','11.000.111/0001-57',NULL,'considerado',NULL,'2025-07-05 15:35:09'),
(157,35,'Painel de Preços',18.79,'EMB c/ 500 FL','2025-07-01','58.037.027 VINICIUS GABRIEL PEREIRA DA SILVA','58037027000139',NULL,'considerado',NULL,'2025-07-05 15:36:21'),
(158,35,'Painel de Preços',19.77,'EMB c/ 500 FL','2025-06-30','MMC SERVICOS DIVERSOS LTDA','54349003000164',NULL,'considerado',NULL,'2025-07-05 15:36:21'),
(159,35,'Painel de Preços',25.00,'EMB c/ 500 FL','2025-06-30','54.619.660 HELIAB GEORGE COUTINHO RUFINO','54619660000184',NULL,'considerado',NULL,'2025-07-05 15:36:21'),
(160,35,'Contratação Similar',22.00,'EMB c/ 500 FL','2025-06-16','CAMARA MUNICIPAL DE PARACAMBI - RJ','26610304000164',NULL,'considerado',NULL,'2025-07-05 15:36:47'),
(161,35,'Contratação Similar',25.10,'EMB c/ 500 FL','2025-06-05','PREFEITURA MUNICIPAL DE BARRA DO PIRAI - RJ','53007570000170',NULL,'considerado',NULL,'2025-07-05 15:36:47'),
(162,35,'Contratação Similar',20.72,'EMB c/ 500 FL','2025-06-05','PREFEITURA MUNICIPAL DE BARRA DO PIRAI - RJ','08198623000203',NULL,'considerado',NULL,'2025-07-05 15:36:47'),
(163,40,'Pesquisa com Fornecedor',28.00,'EMB c/ 500 FL','2025-07-06','Alfa Suprimentos e Comércio EIRELI','11.000.111/0001-57',NULL,'considerado',NULL,'2025-07-06 14:47:22'),
(164,40,'Painel de Preços',18.79,'EMB c/ 500 FL','2025-07-01','58.037.027 VINICIUS GABRIEL PEREIRA DA SILVA','58037027000139',NULL,'considerado',NULL,'2025-07-06 14:48:58'),
(165,40,'Painel de Preços',19.77,'EMB c/ 500 FL','2025-06-30','MMC SERVICOS DIVERSOS LTDA','54349003000164',NULL,'considerado',NULL,'2025-07-06 14:48:58'),
(166,40,'Painel de Preços',25.00,'EMB c/ 500 FL','2025-06-30','54.619.660 HELIAB GEORGE COUTINHO RUFINO','54619660000184',NULL,'considerado',NULL,'2025-07-06 14:48:58'),
(167,40,'Contratação Similar',18.95,'EMB c/ 500 FL','2024-10-24','INSTITUTO NACIONAL DE TRAUMATO-ORTOPEDIA','54362519000149',NULL,'considerado',NULL,'2025-07-06 14:49:32'),
(168,40,'Contratação Similar',22.00,'EMB c/ 500 FL','2025-06-16','CAMARA MUNICIPAL DE PARACAMBI - RJ','26610304000164',NULL,'considerado',NULL,'2025-07-06 14:50:17'),
(169,40,'Contratação Similar',25.10,'EMB c/ 500 FL','2025-06-05','PREFEITURA MUNICIPAL DE BARRA DO PIRAI - RJ','53007570000170',NULL,'considerado',NULL,'2025-07-06 14:50:17'),
(170,40,'Contratação Similar',20.72,'EMB c/ 500 FL','2025-06-05','PREFEITURA MUNICIPAL DE BARRA DO PIRAI - RJ','08198623000203',NULL,'considerado',NULL,'2025-07-06 14:50:17'),
(171,41,'Pesquisa com Fornecedor',28.00,'EMB c/ 500 FL','2025-07-14','Alfa Suprimentos e Comércio EIRELI','11.000.111/0001-57',NULL,'considerado',NULL,'2025-07-14 21:47:43'),
(172,41,'Painel de Preços',19.65,'EMB c/ 500 FL','2025-07-11','MMC SERVICOS DIVERSOS LTDA','54349003000164',NULL,'considerado',NULL,'2025-07-14 21:54:49'),
(173,41,'Painel de Preços',17.00,'EMB c/ 500 FL','2025-07-10','BIGNARDI - INDUSTRIA E COMERCIO DE PAPEIS E ARTEFATOS LTDA.','61192522001018',NULL,'desconsiderado','Valor muito abaixo do mercado podendo gerar licitação deserta','2025-07-14 21:54:49'),
(174,41,'Painel de Preços',19.99,'EMB c/ 500 FL','2025-07-10','A M DE BARROS SOARES LTDA','05346250000100',NULL,'considerado',NULL,'2025-07-14 21:54:49'),
(175,41,'Contratação Similar',22.00,'EMB c/ 500 FL','2025-06-16','CAMARA MUNICIPAL DE PARACAMBI - RJ','26610304000164',NULL,'considerado',NULL,'2025-07-14 21:55:43'),
(176,41,'Contratação Similar',20.72,'EMB c/ 500 FL','2025-06-05','PREFEITURA MUNICIPAL DE BARRA DO PIRAI - RJ','08198623000203',NULL,'considerado',NULL,'2025-07-14 21:55:43'),
(177,41,'Contratação Similar',25.10,'EMB c/ 500 FL','2025-06-05','PREFEITURA MUNICIPAL DE BARRA DO PIRAI - RJ','53007570000170',NULL,'considerado',NULL,'2025-07-14 21:55:43'),
(178,41,'Contratação Similar',18.95,'EMB c/ 500 FL','2024-10-24','INSTITUTO NACIONAL DE TRAUMATO-ORTOPEDIA','54362519000149',NULL,'considerado',NULL,'2025-07-14 21:56:19'),
(179,42,'Pesquisa com Fornecedor',28.00,'resma','2025-08-12','Alfa Suprimentos e Comércio EIRELI','11.000.111/0001-57',NULL,'considerado',NULL,'2025-08-12 12:39:25'),
(180,42,'Painel de Preços',23.05,'EMB c/ 50 FL','2025-04-03','LUASI PAPEIS E LIVROS LTDA','08371036000193',NULL,'considerado',NULL,'2025-08-12 12:43:53'),
(181,42,'Painel de Preços',23.05,'EMB c/ 50 FL','2025-04-03','LUASI PAPEIS E LIVROS LTDA','08371036000193',NULL,'considerado',NULL,'2025-08-12 12:43:53'),
(182,42,'Painel de Preços',29.82,'EMB c/ 100 FL','2023-10-04','MBEM COMERCIO E DISTRIBUICAO DE MATERIAIS ESCOLARES LTDA','39700820000121',NULL,'considerado',NULL,'2025-08-12 12:43:53'),
(183,42,'Contratação Similar',14.70,'EMB c/ 50 FL','2024-10-02','INST.FED.DE EDUC.,CIENC.E TEC.FLUMINENSE','12710145000165',NULL,'desconsiderado','preço abaixo do mercado','2025-08-12 12:45:00'),
(184,42,'Site Especializado',29.70,'resma','2025-08-12','kalunga',NULL,'https://www.kalunga.com.br/prod/papel-sulfite-a4-75g-210mmx297mm-caixa-com-10-resmas-5000-folhas-chamex-cx-5000-fl/996102?srsltid=AfmBOopihLqeQvsMUz0kdeNO-OWU4iXyUoz99X5IhcIuEEy60DW_2uCgDLo','considerado',NULL,'2025-08-12 12:48:07');
/*!40000 ALTER TABLE `precos_coletados` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `processos`
--

DROP TABLE IF EXISTS `processos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `processos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `numero_processo` varchar(255) NOT NULL,
  `nome_processo` varchar(255) NOT NULL,
  `tipo_contratacao` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `agente_responsavel` varchar(255) NOT NULL,
  `agente_matricula` varchar(255) DEFAULT NULL,
  `uasg` varchar(255) NOT NULL,
  `regiao` varchar(255) NOT NULL,
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `justificativa_fontes` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `processos`
--

LOCK TABLES `processos` WRITE;
/*!40000 ALTER TABLE `processos` DISABLE KEYS */;
INSERT INTO `processos` VALUES
(1,'33374.000111/2025-23','Aquisição de material de escritório','Pregão Eletrônico','Em Elaboração','Joabe A. Oliveira','1747222','250042','RJ','2025-06-25 19:51:55',NULL),
(2,'0001/2025','Aquisição de canetas','Pregão Eletrônico','Em Elaboração','Joabe Oliveira','1747222','250042','RJ','2025-06-25 23:48:38','Sdsadsad'),
(3,'25000.000111/2025-21','Aquisição de material de expediente para o órgão UF','Pregão Eletrônico','Em Elaboração','Joabe Oliveira','1747222','250042','RJ','2025-06-28 20:05:58','Não se aplica'),
(4,'001/2025','Aquisição de Notebooks','Pregão Eletrônico','Finalizado','Ana Pereira',NULL,'123456','RJ','2025-03-15 10:00:00',NULL),
(5,'002/2025','Contratação de Material de Limpeza','Dispensa de Licitação','Finalizado','Carlos Souza',NULL,'654321','SP','2025-04-20 11:30:00',NULL),
(6,'003/2025','Serviços de Impressão de Folders','Pregão Eletrônico','Pesquisa em Andamento','Ana Pereira',NULL,'123456','MG','2025-05-10 09:00:00',NULL),
(7,'004/2025','Compra de Desktops e Monitores','Pregão Eletrônico','Pesquisa em Andamento','Mariana Costa',NULL,'123456','RJ','2025-06-01 14:00:00',NULL),
(8,'005/2025','Aquisição de Café e Açúcar','Compra Direta','Finalizado','Carlos Souza',NULL,'654321','SP','2025-05-25 16:00:00',NULL),
(9,'006/2025','Manutenção de Ar Condicionado','Inexigibilidade','Em Elaboração','Roberto Alves',NULL,'789012','BA','2025-06-28 18:00:00',NULL),
(10,'007/2025','Compra de Cadeiras de Escritório','Pregão Eletrônico','Cancelado','Ana Pereira',NULL,'123456','RJ','2025-02-10 12:00:00',NULL),
(11,'25000.2025/01','Aquisição de material de expediente','Pregão Eletrônico','Em Elaboração','Joabe Oliveira','1747222','250042','RJ','2025-07-05 12:46:20',NULL),
(12,'2255-2025-02','aquisição de material hospitalar','Pregão Eletrônico','Em Elaboração','Ana Silva','1747222','250042','RJ','2025-07-05 14:27:14',NULL),
(13,'2025.001-222','aquisição de material de expediente','Pregão Eletrônico','Em Elaboração','Joabe Oliveira','1747222','250042','RJ','2025-07-05 15:29:45',NULL),
(14,'25000.000005/2025-44','Aquisição de insumos hospitalares','Pregão Eletrônico','Em Elaboração','Julio Alves','1747222','250042','RJ','2025-07-06 14:39:42',NULL),
(15,'25000.001002/2025-99','Aquisição de material de expediente','Pregão Eletrônico','Em Elaboração','Julio Alves','1747222','250042','RJ','2025-07-14 21:38:27',NULL),
(16,'20500.1213546456','Aquisição de material de escritório','Pregão Eletrônico','Em Elaboração','Joabe Oliveira','1747222','250042','RJ','2025-08-12 12:16:15',NULL);
/*!40000 ALTER TABLE `processos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES
(1,'joabe antonio de oliveira','joabeantonio@gmail.com','$2y$10$Uj7kdECNG2OcecIJYezpUeaJSnzPNNqU4zrGSkYRoQFQe/YjKEaka','admin','2025-06-24 23:15:08','2025-06-24 23:15:08'),
(3,'Joabe Oliveira','joabeoliveiradev@gmail.com','$2y$10$HfWOYk/Z4bY2keTU.dCV5.jOvgO6y7ucRjw0779FmZTp7RpFo09gm','user','2025-06-29 21:31:51','2025-06-29 21:31:51');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'saas_compras'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2025-08-16 13:27:56
