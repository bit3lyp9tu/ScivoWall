import re
import sys

import json

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


def getFunctionList(file):
    f = open(file, "r")
    return [
        l
        for l in re.findall(
            r"(?<=function\s)\w+(?=\([\s,(\$\w+),\=]*\))",
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


def createReport(test_file):
    json = {}
    source_files = getIncludedFilesAll(test_file)

    json["test_file"] = test_file
    json["source_files_names"] = source_files

    list = {}
    function_all = set()

    for sf in source_files:
        body_data = {}
        body_data["custom_functions"] = len(set(getFunctionList(sf)))
        body_data["function_calls"] = len(set(getFunctionCalls(sf)))
        # body_data["custom_function_calls"] = getReport(sf, test_file)
        body_data["cleaned_calls"] = getCleanedCalls(getFunctionCalls(sf))
        # function_all.update()

        list[sf] = body_data
    json["source_files"] = list

    return json


def main():

    result = createReport(sys.argv[1])

    with open("./tests/test_report.json", "w") as f:
        json.dump(result, f, indent=2)

    # if len(sys.argv) == 2 + 1:
    #     # print(f"Difference of {sys.argv[1]} - {sys.argv[2]}")
    #     for l in getFunctDifference(sys.argv[1], sys.argv[2]):
    #         print(l)
    # else:
    #     print()


if __name__ == "__main__":
    main()
