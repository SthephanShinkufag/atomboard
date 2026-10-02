"""Turn JUnit XML into a GitHub job summary and failure annotations."""

from __future__ import annotations

import os
import json
import re
import sys
from pathlib import Path
from xml.etree import ElementTree


def github_escape(value: str, property_value: bool = False) -> str:
    value = value.replace("%", "%25").replace("\r", "%0D").replace("\n", "%0A")
    return value.replace(",", "%2C").replace(":", "%3A") if property_value else value


def markdown_cell(value: str) -> str:
    return value.replace("|", "\\|").replace("\n", " ").replace("\r", " ")


def source_location(case: ElementTree.Element, detail: str) -> tuple[str, str]:
    path = case.get("file", "")
    line = case.get("line", "")
    match = re.search(r"(?P<path>(?:/[^\s:]+|dev/[^\s:]+)):(?P<line>\d+)", detail)
    if match:
        path = path or match.group("path")
        line = line or match.group("line")
    for prefix in ("/app/", "/var/www/html/test/"):
        if path.startswith(prefix):
            path = path[len(prefix) :]
    if path.startswith("/"):
        try:
            path = str(Path(path).relative_to(Path(os.getenv("GITHUB_WORKSPACE") or Path.cwd())))
        except ValueError:
            return "", ""
    if path.startswith("/") or ".." in Path(path).parts:
        return "", ""
    return path, line if line.isdecimal() else ""


def coverage_line(report: Path) -> str:
    combined = report.parent / "http-summary.json"
    if combined.is_file():
        result = json.loads(combined.read_text(encoding="utf-8"))
        return f"Combined PHP line coverage: **{result['combined_percent']:.2f}%**."
    clover = report.parent / "clover.xml"
    if clover.is_file():
        metrics = ElementTree.parse(clover).getroot().find("project/metrics")
        if metrics is not None:
            total = int(metrics.get("statements", "0"))
            covered = int(metrics.get("coveredstatements", "0"))
            if total:
                return f"CLI PHP line coverage: **{covered / total:.2%}**."
    return ""


def main() -> int:
    if len(sys.argv) != 3:
        print("usage: report-tests.py LABEL JUNIT_XML", file=sys.stderr)
        return 2
    label, report_path = sys.argv[1:]
    report = Path(report_path)
    if not report.is_file():
        summary = f"### {label}\n\nNo JUnit report was produced. Check the test step logs.\n"
        print(summary)
        if os.getenv("GITHUB_STEP_SUMMARY"):
            with open(os.environ["GITHUB_STEP_SUMMARY"], "a", encoding="utf-8") as file:
                file.write(summary)
        return 0

    root = ElementTree.parse(report).getroot()
    cases = root.findall(".//testcase")
    failures: list[tuple[ElementTree.Element, ElementTree.Element]] = []
    skipped = 0
    for case in cases:
        failures.extend((case, failure) for failure in case if failure.tag in ("failure", "error"))
        skipped += int(case.find("skipped") is not None)
    failed_cases = len({id(case) for case, _ in failures})
    passed = len(cases) - failed_cases - skipped

    summary = [
        f"### {label}",
        "",
        "| Passed | Failed | Skipped | Total |",
        "| ---: | ---: | ---: | ---: |",
        f"| {passed} | {failed_cases} | {skipped} | {len(cases)} |",
    ]
    coverage = coverage_line(report)
    if coverage:
        summary.extend(("", coverage))
    if failures:
        summary.extend(("", "| Failed test | Error |", "| --- | --- |"))
    for case, failure in failures:
        name = case.get("name", "Unnamed test")
        detail_lines = (failure.text or "").strip().splitlines()
        message = failure.get("message") or (detail_lines[0] if detail_lines else "Test failed")
        summary.append(f"| {markdown_cell(name)} | {markdown_cell(message)} |")
        path, line = source_location(case, failure.text or "")
        properties = f"title={github_escape(name, True)}"
        if path:
            properties += f",file={github_escape(path, True)}"
        if line:
            properties += f",line={line}"
        print(f"::error {properties}::{github_escape(message)}")
    output = "\n".join(summary) + "\n"
    print(output)
    if os.getenv("GITHUB_STEP_SUMMARY"):
        with open(os.environ["GITHUB_STEP_SUMMARY"], "a", encoding="utf-8") as file:
            file.write(output)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
