import re
import sys

import json
import yaml

from collections import Counter


def getWithoutComments(list):
    l = []
    for line in list:
        i = line.find("//")
        if i != -1:
            l.append(line[:i])
        else:
            l.append(line)
    return l


def getFunctionData(head):
    variables = re.search(r"(?<=\().*(?=\))", head).group().replace(" ", "").split(",")

    return (
        re.search(r"\w+(?=\()", head).group(),
        [] if 0 < len(variables) and variables[0] == "" else variables,
    )


def getFunctionList(file):
    f = open(file, "r")
    return [
        getFunctionData(l)
        for l in re.findall(
            r"(?<=function\s)\w+\([\s,((\.\.\.)?\$\w+),\=,\",\']*\)(?=\s\{)",
            list_to_string(getWithoutComments(f.read().split("\n"))),
        )
    ]


def getFunctionCalls(file):
    f = open(file, "r")
    return [
        l
        for l in re.findall(
            r"\w+(?=\()", list_to_string(getWithoutComments(f.read().split("\n")))
        )
    ]


def getBuildInFuncPHP():
    f = open("./tests/php_build_in_func", "r")
    return {l.replace("\n", "") for l in f.readlines()}


def getFunctDifference(source_file, test_file):
    func = set(getFunctionList(source_file))
    calls = set(getFunctionCalls(test_file))

    build_in_func = getBuildInFuncPHP()

    return (func - build_in_func) - calls


def getReport(source_file, test_file):
    func = getFunctionList(source_file)
    local_func = getFunctionList(test_file)

    calls = getFunctionCalls(test_file)

    build_in_func = getBuildInFuncPHP()
    # res = (func - build_in_func) - calls

    res = Counter(calls)
    for i in build_in_func:
        if i in res:
            res.pop(i)

    print(res)

    return {
        "Func": len(set(func)),
        "LocalFunc": len(local_func),
        "Calls": len(set(calls)),
        "Res": len(res.keys),
        "Diff": (set(res.keys()) - set(func)) - set(local_func),
    }


def getCleanedCalls(calls):
    build_in_func = getBuildInFuncPHP()

    res = Counter(calls)
    for i in build_in_func:
        if i in res:
            res.pop(i)

    return res


def getCallEvaluation(calls, source_files):

    used_functions = []
    unused_functions = []
    all_functions = []

    stack = set()

    for i in source_files.keys():
        file = source_files[i]

        for function, parameters in file:
            data = {
                "name": function,
                "file": i,
                "test_occurrences": calls[function] if function in calls.keys() else 0,
                "parameters": parameters,
            }

            if function in calls.keys():
                used_functions.append(data)
            else:
                unused_functions.append(data)
            all_functions.append(data)
            stack.add(function)

    return (
        all_functions,
        used_functions,
        unused_functions,
        [i for i in (calls.keys() - stack)],
    )


def getTestScore(a, b, c, all_functions):
    scores = {}

    max_score = 0
    score = 0

    for item in all_functions:
        name, _, test_occurrences, parameters = item

        param = len(item[parameters])
        extra = [i for i in item[parameters] if "..." in i or "=" in i]

        specified = 1 * a + param * b + len(extra) * c
        occurred = item[test_occurrences]

        delta = 0 if specified <= occurred else specified - occurred

        max_score += specified
        score += delta

        scores[item[name]] = {
            "specified_occurrences": specified,
            "test_occurrences": occurred,
            "delta": delta,
        }

    return score, max_score, scores


def getUntestedFunctions(file, function_all):
    local_func = getFunctionList(file)

    build_in_func = getBuildInFuncPHP()

    res = Counter(calls)
    for i in build_in_func:
        if i in res:
            res.pop(i)

    return function_all


def list_to_string(list):
    str = ""
    for i in list:
        str += i

    return str


def getIncludedFiles(target_file):
    f = open(target_file, "r")
    lines = list_to_string(getWithoutComments(f.read().split("\n")))

    l = re.findall(r"(?<=include\(\")[\w+,\_,\-]+\.\w+(?=\"\)\;)", lines)
    l.extend(re.findall(r"(?<=include_once\(\")[\w+,\_,\-]+\.\w+(?=\"\)\;)", lines))

    return l


def getIncludedFilesAll(file):
    l = getIncludedFiles(file)
    visited_files = []
    results = set()

    while 0 < len(l):
        if l[0] not in visited_files:
            res = getIncludedFiles(l[0])
            visited_files.append(l[0])
            l.extend(res)
            results.add(l[0])
            results.update(res)

        l.pop(0)

    return visited_files


def createReport(test_file, a, b, c):
    source_files = getIncludedFilesAll(test_file)
    result = {}

    result["test_file"] = test_file
    result["source_files_names"] = source_files

    list = {}
    list[test_file] = getFunctionList(test_file)
    for sf in source_files:
        list[sf] = getFunctionList(sf)

    calls = getCleanedCalls(getFunctionCalls(test_file))

    all, used, unused, other = getCallEvaluation(calls, list)

    result["all"] = all
    result["used"] = used
    result["unused"] = unused

    result["calls_in_test"] = calls
    result["sourceles_calls"] = other

    score, max, test_scores = getTestScore(a, b, c, all)

    result["test_scores"] = test_scores

    result["summary"] = {
        "test_file_name": test_file,
        "source_files": len(source_files),
        "calls_in_test": len(calls),
        "found_source_functions": len(all),
        "sourceless_calls": len(other),
        "score": len(test_scores.keys()),
        "calc_score": score,
        "max_score": max,
        "percentage": round((max - score) / max * 100, 3),
    }

    return result


def main():

    if len(sys.argv) >= 1 + 1:

        if len(sys.argv) == 1 + 1:
            result = createReport(sys.argv[1], 1, 0, 0)
        else:
            result = createReport(
                sys.argv[1], int(sys.argv[2]), int(sys.argv[3]), int(sys.argv[4])
            )

        with open("./tests/test_report.json", "w") as f:
            json.dump(result, f, indent=2)

        print(json.dumps(result["summary"], indent=2))

    else:
        print("Error")

    # if len(sys.argv) == 2 + 1:
    #     # print(f"Difference of {sys.argv[1]} - {sys.argv[2]}")
    #     for l in getFunctDifference(sys.argv[1], sys.argv[2]):
    #         print(l)
    # else:
    #     print()


if __name__ == "__main__":
    main()
