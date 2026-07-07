-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 07/07/2026 às 16:29
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `estagio`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `cargos`
--

CREATE TABLE `cargos` (
  `ID` int(11) NOT NULL,
  `NOME` varchar(255) NOT NULL,
  `id_empresa` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nome` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `categorias`
--

INSERT INTO `categorias` (`id`, `nome`) VALUES
(1, 'ADMIN'),
(2, 'FUNCIONARIO'),
(3, 'EXPOSITOR');

-- --------------------------------------------------------

--
-- Estrutura para tabela `empresas`
--

CREATE TABLE `empresas` (
  `ID` int(11) NOT NULL,
  `ANO` int(11) DEFAULT NULL,
  `NOME_FANTASIA` varchar(255) DEFAULT NULL,
  `RAZAO_SOCIAL` varchar(255) DEFAULT NULL,
  `CNPJ` varchar(11) DEFAULT NULL,
  `CRIADO_EM` timestamp NOT NULL DEFAULT current_timestamp(),
  `ATUALIZADO_EM` timestamp NULL DEFAULT NULL,
  `EXCLUIDO_EM` timestamp NULL DEFAULT NULL,
  `QUANTIDADE_ESPACOS` int(11) NOT NULL,
  `TIPO_ID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pessoas`
--

CREATE TABLE `pessoas` (
  `ID` int(11) NOT NULL,
  `EMPRESA_ID` int(11) DEFAULT NULL,
  `NOME` varchar(255) DEFAULT NULL,
  `INGRESSO_PERMANENTE` char(1) NOT NULL,
  `FOTO` varchar(255) NOT NULL,
  `CPF` char(11) NOT NULL,
  `DOCUMENTO` varchar(30) NOT NULL,
  `TELEFONE` varchar(14) NOT NULL,
  `CRIADO_EM` timestamp NULL DEFAULT current_timestamp(),
  `ATUALIZADO_EM` timestamp NULL DEFAULT NULL,
  `EXCLUIDO_EM` timestamp NULL DEFAULT NULL,
  `CARGO_ID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tipos`
--

CREATE TABLE `tipos` (
  `ID` int(11) NOT NULL,
  `NOME` varchar(250) DEFAULT NULL,
  `CONTROLA_ESPACOS` char(1) NOT NULL,
  `LIMITE_PESSOAS` int(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tipos`
--

INSERT INTO `tipos` (`ID`, `NOME`, `CONTROLA_ESPACOS`, `LIMITE_PESSOAS`) VALUES
(90, 'Comissão', 'N', 0),
(91, 'Casa Cultural', 'N', 0),
(92, 'Outros', 'N', 0),
(93, 'Imprensa', 'N', 0),
(94, 'Prefeitura de Ijuí', 'N', 0),
(95, 'Ueti / Expofest', 'N', 0),
(96, 'SHOW', 'N', 0),
(97, 'Empresa', 'N', 0),
(98, 'Expositor', 'N', 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `ID` int(11) NOT NULL,
  `EMAIL` varchar(250) DEFAULT NULL,
  `NOME` varchar(250) DEFAULT NULL,
  `SENHA` varchar(250) DEFAULT NULL,
  `CATEGORIA_ID` int(11) DEFAULT NULL,
  `EMPRESA_ID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`ID`, `EMAIL`, `NOME`, `SENHA`, `CATEGORIA_ID`, `EMPRESA_ID`) VALUES
(24, 'teste@gmail.com', 'teste', 'e10adc3949ba59abbe56e057f20f883e', 1, 0);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `cargos`
--
ALTER TABLE `cargos`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `id_empresa` (`id_empresa`);

--
-- Índices de tabela `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `empresas`
--
ALTER TABLE `empresas`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `TIPO_ID` (`TIPO_ID`);

--
-- Índices de tabela `pessoas`
--
ALTER TABLE `pessoas`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `uk_empresa_cpf` (`EMPRESA_ID`,`CPF`),
  ADD KEY `CARGO_ID` (`CARGO_ID`);

--
-- Índices de tabela `tipos`
--
ALTER TABLE `tipos`
  ADD PRIMARY KEY (`ID`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `cargos`
--
ALTER TABLE `cargos`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=134;

--
-- AUTO_INCREMENT de tabela `empresas`
--
ALTER TABLE `empresas`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11501;

--
-- AUTO_INCREMENT de tabela `pessoas`
--
ALTER TABLE `pessoas`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=779;

--
-- AUTO_INCREMENT de tabela `tipos`
--
ALTER TABLE `tipos`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `cargos`
--
ALTER TABLE `cargos`
  ADD CONSTRAINT `cargos_ibfk_2` FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`ID`) ON UPDATE CASCADE;

--
-- Restrições para tabelas `empresas`
--
ALTER TABLE `empresas`
  ADD CONSTRAINT `empresas_ibfk_1` FOREIGN KEY (`TIPO_ID`) REFERENCES `tipos` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `pessoas`
--
ALTER TABLE `pessoas`
  ADD CONSTRAINT `pessoas_ibfk_1` FOREIGN KEY (`CARGO_ID`) REFERENCES `cargos` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
